package com.finaxis.finaxis_mobile.data.repository

import com.finaxis.finaxis_mobile.data.local.TokenStore
import com.finaxis.finaxis_mobile.data.remote.ApiResult
import com.finaxis.finaxis_mobile.data.remote.ApiService
import com.finaxis.finaxis_mobile.data.remote.dto.AuthResponse
import com.finaxis.finaxis_mobile.data.remote.dto.LoginRequest
import com.finaxis.finaxis_mobile.data.remote.dto.RegisterRequest
import com.finaxis.finaxis_mobile.data.remote.dto.UserDto
import com.finaxis.finaxis_mobile.data.remote.safeApiCall

/**
 * Tudo que envolve login/sessão passa por aqui.
 * As telas não falam direto com o Retrofit nem com o DataStore.
 */
class AuthRepository(
    private val api: ApiService,
    private val tokenStore: TokenStore,
) {

    suspend fun hasSavedToken(): Boolean = !tokenStore.currentToken().isNullOrBlank()

    suspend fun login(email: String, password: String): ApiResult<UserDto?> =
        saveIfSuccess(safeApiCall { api.login(LoginRequest(email.trim(), password)) })

    suspend fun register(
        name: String,
        email: String,
        password: String,
        passwordConfirmation: String,
    ): ApiResult<UserDto?> = saveIfSuccess(
        safeApiCall {
            api.register(RegisterRequest(name.trim(), email.trim(), password, passwordConfirmation))
        }
    )

    /** Confere com a API se o token salvo ainda vale. Se vier 401, o interceptor já limpa a sessão. */
    suspend fun currentUser(): ApiResult<UserDto> = safeApiCall { api.me() }

    /** Avisa a API (se der) e apaga o token do aparelho de qualquer jeito. */
    suspend fun logout() {
        safeApiCall { api.logout() }
        tokenStore.clear()
    }

    private suspend fun saveIfSuccess(result: ApiResult<AuthResponse>): ApiResult<UserDto?> =
        when (result) {
            is ApiResult.Success -> {
                val token = result.data.accessToken
                if (token.isNullOrBlank()) {
                    ApiResult.HttpError(200, "A API não devolveu o token.")
                } else {
                    tokenStore.saveSession(token, result.data.user?.name)
                    ApiResult.Success(result.data.user)
                }
            }
            is ApiResult.HttpError -> result
            is ApiResult.NetworkError -> result
        }
}
