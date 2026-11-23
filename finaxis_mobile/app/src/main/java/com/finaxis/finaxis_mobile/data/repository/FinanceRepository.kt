package com.finaxis.finaxis_mobile.data.repository

import com.finaxis.finaxis_mobile.data.remote.ApiResult
import com.finaxis.finaxis_mobile.data.remote.ApiService
import com.finaxis.finaxis_mobile.data.remote.dto.AccountDto
import com.finaxis.finaxis_mobile.data.remote.dto.AccountRequest
import com.finaxis.finaxis_mobile.data.remote.dto.CategoryDto
import com.finaxis.finaxis_mobile.data.remote.dto.ContributionRequest
import com.finaxis.finaxis_mobile.data.remote.dto.ContributionResponse
import com.finaxis.finaxis_mobile.data.remote.dto.DashboardDto
import com.finaxis.finaxis_mobile.data.remote.dto.GoalDto
import com.finaxis.finaxis_mobile.data.remote.dto.GoalRequest
import com.finaxis.finaxis_mobile.data.remote.dto.TransactionDto
import com.finaxis.finaxis_mobile.data.remote.dto.TransactionRequest
import com.finaxis.finaxis_mobile.data.remote.safeApiCall
import com.finaxis.finaxis_mobile.data.remote.safeApiCallNoBody

/**
 * Ponto único para as telas buscarem e alterarem dados financeiros.
 * Cada função devolve um ApiResult, então a tela nunca recebe exceção.
 */
class FinanceRepository(private val api: ApiService) {

    suspend fun dashboard(): ApiResult<DashboardDto> = safeApiCall { api.dashboard() }

    // Contas
    suspend fun accounts(): ApiResult<List<AccountDto>> = safeApiCall { api.accounts() }
    suspend fun createAccount(body: AccountRequest): ApiResult<AccountDto> = safeApiCall { api.createAccount(body) }
    suspend fun updateAccount(id: Long, body: AccountRequest): ApiResult<AccountDto> =
        safeApiCall { api.updateAccount(id, body) }
    suspend fun deleteAccount(id: Long): ApiResult<Unit> = safeApiCallNoBody { api.deleteAccount(id) }

    // Categorias
    suspend fun categories(): ApiResult<List<CategoryDto>> = safeApiCall { api.categories() }

    // Transações
    suspend fun transactions(type: String? = null): ApiResult<List<TransactionDto>> =
        safeApiCall { api.transactions(type) }
    suspend fun createTransaction(body: TransactionRequest): ApiResult<TransactionDto> =
        safeApiCall { api.createTransaction(body) }
    suspend fun updateTransaction(id: Long, body: TransactionRequest): ApiResult<TransactionDto> =
        safeApiCall { api.updateTransaction(id, body) }
    suspend fun deleteTransaction(id: Long): ApiResult<Unit> = safeApiCallNoBody { api.deleteTransaction(id) }

    // Metas
    suspend fun goals(): ApiResult<List<GoalDto>> = safeApiCall { api.goals() }
    suspend fun goal(id: Long): ApiResult<GoalDto> = safeApiCall { api.goal(id) }
    suspend fun createGoal(body: GoalRequest): ApiResult<GoalDto> = safeApiCall { api.createGoal(body) }
    suspend fun updateGoal(id: Long, body: GoalRequest): ApiResult<GoalDto> = safeApiCall { api.updateGoal(id, body) }
    suspend fun deleteGoal(id: Long): ApiResult<Unit> = safeApiCallNoBody { api.deleteGoal(id) }
    suspend fun contribute(goalId: Long, body: ContributionRequest): ApiResult<ContributionResponse> =
        safeApiCall { api.contribute(goalId, body) }
    suspend fun removeContribution(goalId: Long, transactionId: Long): ApiResult<Unit> =
        safeApiCallNoBody { api.removeContribution(goalId, transactionId) }
}
