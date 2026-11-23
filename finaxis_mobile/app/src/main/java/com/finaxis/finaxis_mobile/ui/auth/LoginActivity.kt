package com.finaxis.finaxis_mobile.ui.auth

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.view.inputmethod.EditorInfo
import android.widget.Toast
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.isVisible
import androidx.core.view.updatePadding
import androidx.core.widget.doAfterTextChanged
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import androidx.lifecycle.viewmodel.initializer
import androidx.lifecycle.viewmodel.viewModelFactory
import com.finaxis.finaxis_mobile.FinaxisApp
import com.finaxis.finaxis_mobile.MainActivity
import com.finaxis.finaxis_mobile.R
import com.finaxis.finaxis_mobile.databinding.ActivityLoginBinding
import com.finaxis.finaxis_mobile.util.enableFinaxisEdgeToEdge
import kotlinx.coroutines.launch

class LoginActivity : AppCompatActivity() {

    private lateinit var binding: ActivityLoginBinding

    // Cria o ViewModel passando o repositório que mora na FinaxisApp.
    private val viewModel: LoginViewModel by viewModels {
        viewModelFactory {
            initializer { LoginViewModel((application as FinaxisApp).authRepository) }
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        enableFinaxisEdgeToEdge()
        super.onCreate(savedInstanceState)
        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        // Afasta o conteúdo das barras do sistema e do teclado (que também é um "inset").
        ViewCompat.setOnApplyWindowInsetsListener(binding.root) { view, insets ->
            val bars = insets.getInsets(
                WindowInsetsCompat.Type.systemBars() or WindowInsetsCompat.Type.displayCutout()
            )
            val keyboard = insets.getInsets(WindowInsetsCompat.Type.ime())
            view.updatePadding(
                left = bars.left,
                top = bars.top,
                right = bars.right,
                bottom = maxOf(bars.bottom, keyboard.bottom),
            )
            insets
        }

        binding.btnLogin.setOnClickListener { submit() }

        // "Enter" no teclado, no campo de senha, também faz login.
        binding.edtPassword.setOnEditorActionListener { _, actionId, _ ->
            if (actionId == EditorInfo.IME_ACTION_DONE) {
                submit()
                true
            } else {
                false
            }
        }

        // Ao digitar de novo, some a mensagem de erro do campo.
        binding.edtEmail.doAfterTextChanged { binding.tilEmail.error = null }
        binding.edtPassword.doAfterTextChanged { binding.tilPassword.error = null }

        binding.txtGoToRegister.setOnClickListener {
            // A tela de cadastro será feita na próxima etapa.
            Toast.makeText(this, "Cadastro pelo app em breve. Use o site por enquanto.", Toast.LENGTH_SHORT).show()
        }

        // Observa o estado do ViewModel enquanto a tela está visível.
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                viewModel.state.collect { render(it) }
            }
        }
    }

    private fun submit() {
        viewModel.login(
            email = binding.edtEmail.text?.toString().orEmpty(),
            password = binding.edtPassword.text?.toString().orEmpty(),
        )
    }

    private fun render(state: LoginUiState) {
        if (state.loggedIn) {
            openMain()
            return
        }

        // Enquanto confere o token salvo, mostra só o carregamento.
        binding.progressSession.isVisible = state.checkingSession
        binding.formContainer.visibility = if (state.checkingSession) View.INVISIBLE else View.VISIBLE

        binding.progressLogin.isVisible = state.loading
        binding.btnLogin.isEnabled = !state.loading
        binding.btnLogin.text = if (state.loading) "" else getString(R.string.login_button)

        binding.tilEmail.error = state.emailError
        binding.tilPassword.error = state.passwordError

        binding.txtError.text = state.generalError
        binding.txtError.isVisible = state.generalError != null
    }

    private fun openMain() {
        startActivity(Intent(this, MainActivity::class.java))
        finish() // Tira o login da pilha: o "voltar" não retorna para cá.
    }
}
