package com.finaxis.finaxis_mobile.data.remote

import com.finaxis.finaxis_mobile.data.remote.dto.ApiErrorBody
import com.google.gson.Gson
import retrofit2.Response
import java.io.IOException

/**
 * Resultado de uma chamada à API, já tratado. A tela só precisa olhar qual dos casos veio.
 */
sealed class ApiResult<out T> {

    data class Success<T>(val data: T) : ApiResult<T>()

    /**
     * A API respondeu com erro (401, 404, 422, 500...).
     * fieldErrors vem preenchido no 422, ex.: {"email": ["E-mail inválido."]}
     */
    data class HttpError(
        val code: Int,
        val message: String,
        val fieldErrors: Map<String, List<String>> = emptyMap(),
    ) : ApiResult<Nothing>()

    /** Não chegou na API: Wi-Fi, IP errado, XAMPP desligado, firewall... */
    data class NetworkError(val message: String) : ApiResult<Nothing>()
}

private val gson = Gson()

/**
 * Executa a chamada e converte qualquer resultado num ApiResult,
 * sem deixar exceção escapar para a tela.
 */
suspend fun <T> safeApiCall(call: suspend () -> Response<T>): ApiResult<T> {
    return try {
        val response = call()
        val body = response.body()
        if (response.isSuccessful && body != null) {
            ApiResult.Success(body)
        } else {
            val error = parseError(response)
            ApiResult.HttpError(
                code = response.code(),
                message = error?.message ?: defaultMessage(response.code()),
                fieldErrors = error?.errors.orEmpty(),
            )
        }
    } catch (e: IOException) {
        ApiResult.NetworkError(
            "Não foi possível conectar ao servidor. Confira se o XAMPP está ligado " +
                "e se o celular está na mesma rede Wi-Fi do PC."
        )
    } catch (e: Exception) {
        ApiResult.NetworkError("Erro inesperado: ${e.message}")
    }
}

/**
 * Igual ao safeApiCall, mas para respostas sem corpo (204 das exclusões).
 */
suspend fun safeApiCallNoBody(call: suspend () -> Response<Unit>): ApiResult<Unit> {
    return try {
        val response = call()
        if (response.isSuccessful) {
            ApiResult.Success(Unit)
        } else {
            val error = parseError(response)
            ApiResult.HttpError(
                code = response.code(),
                message = error?.message ?: defaultMessage(response.code()),
                fieldErrors = error?.errors.orEmpty(),
            )
        }
    } catch (e: IOException) {
        ApiResult.NetworkError(
            "Não foi possível conectar ao servidor. Confira se o XAMPP está ligado " +
                "e se o celular está na mesma rede Wi-Fi do PC."
        )
    } catch (e: Exception) {
        ApiResult.NetworkError("Erro inesperado: ${e.message}")
    }
}

/** Texto único para mostrar ao usuário (mensagem + primeiro erro de cada campo). */
fun ApiResult<*>.errorText(): String? = when (this) {
    is ApiResult.Success -> null
    is ApiResult.NetworkError -> message
    is ApiResult.HttpError ->
        if (fieldErrors.isEmpty()) message
        else fieldErrors.values.mapNotNull { it.firstOrNull() }.joinToString("\n")
}

private fun parseError(response: Response<*>): ApiErrorBody? = try {
    response.errorBody()?.string()?.let { gson.fromJson(it, ApiErrorBody::class.java) }
} catch (e: Exception) {
    null // A resposta não era JSON (ex.: página de erro do Apache)
}

private fun defaultMessage(code: Int): String = when (code) {
    401 -> "Sessão expirada. Faça login novamente."
    404 -> "Endereço da API não encontrado. Confira o finaxis.apiUrl no local.properties."
    422 -> "Dados inválidos."
    in 500..599 -> "Erro interno do servidor."
    else -> "Erro $code ao falar com o servidor."
}
