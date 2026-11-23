package com.finaxis.finaxis_mobile.ui.auth

import android.util.Patterns
import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.finaxis.finaxis_mobile.data.remote.ApiResult
import com.finaxis.finaxis_mobile.data.repository.AuthRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.flow.update
import kotlinx.coroutines.launch

/** Tudo que a tela de login precisa desenhar. */
data class LoginUiState(
    val checkingSession: Boolean = true,
    val loading: Boolean = false,
    val emailError: String? = null,
    val passwordError: String? = null,
    val generalError: String? = null,
    val loggedIn: Boolean = false,
)

/**
 * Regras da tela de login. Fica separado da Activity para sobreviver
 * à rotação da tela e para a Activity cuidar só do visual.
 */
class LoginViewModel(private val repository: AuthRepository) : ViewModel() {

    private val _state = MutableStateFlow(LoginUiState())
    val state: StateFlow<LoginUiState> = _state.asStateFlow()

    init {
        checkSavedSession()
    }

    /** Ao abrir o app: se já existe token, pergunta à API se ele ainda vale. */
    private fun checkSavedSession() {
        viewModelScope.launch {
            if (!repository.hasSavedToken()) {
                _state.update { it.copy(checkingSession = false) }
                return@launch
            }
            when (val result = repository.currentUser()) {
                is ApiResult.Success -> _state.update { it.copy(checkingSession = false, loggedIn = true) }
                is ApiResult.HttpError -> _state.update {
                    // 401: o interceptor já apagou o token. Mostra o formulário.
                    it.copy(checkingSession = false, generalError = if (result.code == 401) null else result.message)
                }
                is ApiResult.NetworkError -> _state.update {
                    it.copy(checkingSession = false, generalError = result.message)
                }
            }
        }
    }

    fun login(email: String, password: String) {
        if (_state.value.loading) return

        // Mesma validação da API, feita antes para não gastar uma requisição à toa.
        val emailError = when {
            email.isBlank() -> "Informe o e-mail."
            !Patterns.EMAIL_ADDRESS.matcher(email.trim()).matches() -> "E-mail inválido."
            else -> null
        }
        val passwordError = if (password.isEmpty()) "Informe a senha." else null
        if (emailError != null || passwordError != null) {
            _state.update {
                it.copy(emailError = emailError, passwordError = passwordError, generalError = null)
            }
            return
        }

        _state.update { it.copy(loading = true, emailError = null, passwordError = null, generalError = null) }

        viewModelScope.launch {
            when (val result = repository.login(email, password)) {
                is ApiResult.Success -> _state.update { it.copy(loading = false, loggedIn = true) }
                is ApiResult.HttpError -> _state.update {
                    it.copy(
                        loading = false,
                        emailError = result.fieldErrors["email"]?.firstOrNull(),
                        passwordError = result.fieldErrors["password"]?.firstOrNull(),
                        // 401 aqui = e-mail ou senha incorretos (mensagem vem da API)
                        generalError = if (result.fieldErrors.isEmpty()) result.message else null,
                    )
                }
                is ApiResult.NetworkError -> _state.update {
                    it.copy(loading = false, generalError = result.message)
                }
            }
        }
    }
}
