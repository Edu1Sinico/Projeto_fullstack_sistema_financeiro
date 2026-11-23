package com.finaxis.finaxis_mobile.data.remote

import com.finaxis.finaxis_mobile.data.remote.dto.AccountDto
import com.finaxis.finaxis_mobile.data.remote.dto.AccountRequest
import com.finaxis.finaxis_mobile.data.remote.dto.AuthResponse
import com.finaxis.finaxis_mobile.data.remote.dto.CategoryDto
import com.finaxis.finaxis_mobile.data.remote.dto.ContributionRequest
import com.finaxis.finaxis_mobile.data.remote.dto.ContributionResponse
import com.finaxis.finaxis_mobile.data.remote.dto.DashboardDto
import com.finaxis.finaxis_mobile.data.remote.dto.GoalDto
import com.finaxis.finaxis_mobile.data.remote.dto.GoalRequest
import com.finaxis.finaxis_mobile.data.remote.dto.LoginRequest
import com.finaxis.finaxis_mobile.data.remote.dto.MessageResponse
import com.finaxis.finaxis_mobile.data.remote.dto.RegisterRequest
import com.finaxis.finaxis_mobile.data.remote.dto.TransactionDto
import com.finaxis.finaxis_mobile.data.remote.dto.TransactionRequest
import com.finaxis.finaxis_mobile.data.remote.dto.UserDto
import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Path
import retrofit2.http.Query

/**
 * Lista dos endpoints da API PHP. O Retrofit gera o código das chamadas a partir daqui.
 * Os caminhos são relativos à BASE_URL (…/sistema_financeiro/api/), por isso não começam com "/".
 *
 * As exclusões respondem 204 (sem corpo), por isso usam Response<Unit>.
 */
interface ApiService {

    @POST("auth/login")
    suspend fun login(@Body body: LoginRequest): Response<AuthResponse>

    @POST("auth/register")
    suspend fun register(@Body body: RegisterRequest): Response<AuthResponse>

    @GET("auth/me")
    suspend fun me(): Response<UserDto>

    @POST("auth/refresh")
    suspend fun refresh(): Response<AuthResponse>

    @POST("auth/logout")
    suspend fun logout(): Response<MessageResponse>

    // ---------- Dashboard ----------

    @GET("dashboard")
    suspend fun dashboard(): Response<DashboardDto>

    // ---------- Contas ----------

    @GET("accounts")
    suspend fun accounts(): Response<List<AccountDto>>

    @POST("accounts")
    suspend fun createAccount(@Body body: AccountRequest): Response<AccountDto>

    @PUT("accounts/{id}")
    suspend fun updateAccount(@Path("id") id: Long, @Body body: AccountRequest): Response<AccountDto>

    @DELETE("accounts/{id}")
    suspend fun deleteAccount(@Path("id") id: Long): Response<Unit>

    // ---------- Categorias ----------

    @GET("categories")
    suspend fun categories(): Response<List<CategoryDto>>

    // ---------- Transações ----------

    /** type = "income", "expense" ou null (todas). Parâmetro null não é enviado. */
    @GET("transactions")
    suspend fun transactions(@Query("type") type: String? = null): Response<List<TransactionDto>>

    @POST("transactions")
    suspend fun createTransaction(@Body body: TransactionRequest): Response<TransactionDto>

    @PUT("transactions/{id}")
    suspend fun updateTransaction(@Path("id") id: Long, @Body body: TransactionRequest): Response<TransactionDto>

    @DELETE("transactions/{id}")
    suspend fun deleteTransaction(@Path("id") id: Long): Response<Unit>

    // ---------- Metas ----------

    @GET("goals")
    suspend fun goals(): Response<List<GoalDto>>

    @GET("goals/{id}")
    suspend fun goal(@Path("id") id: Long): Response<GoalDto>

    @POST("goals")
    suspend fun createGoal(@Body body: GoalRequest): Response<GoalDto>

    @PUT("goals/{id}")
    suspend fun updateGoal(@Path("id") id: Long, @Body body: GoalRequest): Response<GoalDto>

    @DELETE("goals/{id}")
    suspend fun deleteGoal(@Path("id") id: Long): Response<Unit>

    @POST("goals/{id}/contributions")
    suspend fun contribute(@Path("id") id: Long, @Body body: ContributionRequest): Response<ContributionResponse>

    @DELETE("goals/{goalId}/contributions/{transactionId}")
    suspend fun removeContribution(
        @Path("goalId") goalId: Long,
        @Path("transactionId") transactionId: Long,
    ): Response<Unit>
}
