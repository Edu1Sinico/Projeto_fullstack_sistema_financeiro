package com.finaxis.finaxis_mobile.data.remote

import com.finaxis.finaxis_mobile.data.local.TokenStore
import kotlinx.coroutines.runBlocking
import okhttp3.Interceptor
import okhttp3.Response

/**
 * Roda antes de cada requisição:
 * 1. Sempre pede JSON (Accept: application/json).
 * 2. Se houver token salvo, envia "Authorization: Bearer <token>".
 * 3. Se a API responder 401 numa rota protegida, o token venceu: apaga a sessão
 *    e avisa o app pelo SessionEvents, para voltar ao login.
 *
 * O runBlocking aqui é seguro porque o OkHttp chama o interceptor numa thread de rede,
 * nunca na thread principal da tela.
 */
class AuthInterceptor(private val tokenStore: TokenStore) : Interceptor {

    override fun intercept(chain: Interceptor.Chain): Response {
        val token = runBlocking { tokenStore.currentToken() }

        val request = chain.request().newBuilder()
            .header("Accept", "application/json")
            .apply { if (!token.isNullOrBlank()) header("Authorization", "Bearer $token") }
            .build()

        val response = chain.proceed(request)

        if (response.code == 401 && !token.isNullOrBlank()) {
            runBlocking { tokenStore.clear() }
            SessionEvents.notifyExpired()
        }

        return response
    }
}
