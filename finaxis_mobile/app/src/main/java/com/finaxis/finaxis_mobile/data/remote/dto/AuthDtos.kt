package com.finaxis.finaxis_mobile.data.remote.dto

import com.google.gson.annotations.SerializedName

/*
 * DTOs = "Data Transfer Objects": classes que espelham exatamente o JSON da API.
 * Os campos das respostas são anuláveis (?) porque o Gson não garante que o JSON
 * venha completo, e assim evitamos crash se a API mudar algum campo.
 */

/** Corpo de POST auth/login */
data class LoginRequest(
    @SerializedName("email") val email: String,
    @SerializedName("password") val password: String,
)

/** Corpo de POST auth/register */
data class RegisterRequest(
    @SerializedName("name") val name: String,
    @SerializedName("email") val email: String,
    @SerializedName("password") val password: String,
    @SerializedName("password_confirmation") val passwordConfirmation: String,
)

/** Resposta de login, register e refresh */
data class AuthResponse(
    @SerializedName("access_token") val accessToken: String?,
    @SerializedName("token_type") val tokenType: String?,
    @SerializedName("expires_in") val expiresIn: Long?,
    @SerializedName("user") val user: UserDto?,
)

/** Usuário como a API devolve (sem a senha) */
data class UserDto(
    @SerializedName("id") val id: Long?,
    @SerializedName("name") val name: String?,
    @SerializedName("email") val email: String?,
    @SerializedName("created_at") val createdAt: String?,
    @SerializedName("updated_at") val updatedAt: String?,
)

/** Resposta simples com mensagem, por exemplo a do logout */
data class MessageResponse(
    @SerializedName("message") val message: String?,
)

/**
 * Formato dos erros da API.
 * 401/404/500: só "message".
 * 422 (validação): "message" + "errors", ex.: {"email": ["Este e-mail já está cadastrado."]}
 */
data class ApiErrorBody(
    @SerializedName("message") val message: String?,
    @SerializedName("errors") val errors: Map<String, List<String>>?,
)
