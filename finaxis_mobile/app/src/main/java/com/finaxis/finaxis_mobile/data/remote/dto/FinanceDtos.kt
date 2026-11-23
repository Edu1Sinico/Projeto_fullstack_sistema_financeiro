package com.finaxis.finaxis_mobile.data.remote.dto

import com.google.gson.annotations.SerializedName

/*
 * Valores em dinheiro ficam como String: a API às vezes manda "150.00" (texto) e às vezes 150.5 (número).
 * O Gson aceita os dois num campo String, e depois o Money.parse() converte para BigDecimal,
 * que não tem os erros de arredondamento do Double.
 */

data class AccountDto(
    @SerializedName("id") val id: Long?,
    @SerializedName("name") val name: String?,
    @SerializedName("type") val type: String?, // checking | savings | wallet
    @SerializedName("status") val status: String?, // active | inactive
    @SerializedName("balance") val balance: String?,
)

data class CategoryDto(
    @SerializedName("id") val id: Long?,
    @SerializedName("name") val name: String?,
    @SerializedName("type") val type: String?, // income | expense
    @SerializedName("status") val status: String?,
)

data class TransactionDto(
    @SerializedName("id") val id: Long?,
    @SerializedName("account_id") val accountId: Long?,
    @SerializedName("category_id") val categoryId: Long?,
    @SerializedName("description") val description: String?,
    @SerializedName("amount") val amount: String?,
    @SerializedName("type") val type: String?, // income | expense
    @SerializedName("transaction_date") val transactionDate: String?, // 2026-11-19
    @SerializedName("account_name") val accountName: String?,
    @SerializedName("category_name") val categoryName: String?,
    @SerializedName("goals_count") val goalsCount: Int?,
    // Só vem dentro de GET goals/{id}: quanto dessa transação foi para a meta
    @SerializedName("contributed_amount") val contributedAmount: String?,
)

data class GoalDto(
    @SerializedName("id") val id: Long?,
    @SerializedName("title") val title: String?,
    @SerializedName("target_amount") val targetAmount: String?,
    @SerializedName("current_amount") val currentAmount: String?,
    @SerializedName("deadline") val deadline: String?,
    @SerializedName("completed") val completed: Boolean?,
    @SerializedName("progress_percentage") val progressPercentage: Double?,
    // Só vem em GET goals/{id}: as contribuições feitas
    @SerializedName("transactions") val transactions: List<TransactionDto>?,
)

/** Resposta de GET dashboard */
data class DashboardDto(
    @SerializedName("balance") val balance: String?,
    @SerializedName("income") val income: String?,
    @SerializedName("expenses") val expenses: String?,
    @SerializedName("result") val result: String?,
    @SerializedName("accounts") val accounts: List<AccountDto>?,
    @SerializedName("recent_transactions") val recentTransactions: List<TransactionDto>?,
    @SerializedName("goals") val goals: List<GoalDto>?,
)

// ---------- Corpos enviados para a API ----------
// Campos null não são enviados (o Gson pula nulls).

data class AccountRequest(
    @SerializedName("name") val name: String,
    @SerializedName("type") val type: String,
    @SerializedName("status") val status: String? = null,
    // Só no cadastro. Depois o saldo muda apenas por transações.
    @SerializedName("balance") val balance: String? = null,
)

data class TransactionRequest(
    @SerializedName("account_id") val accountId: Long,
    @SerializedName("category_id") val categoryId: Long,
    @SerializedName("description") val description: String,
    @SerializedName("amount") val amount: String,
    @SerializedName("type") val type: String,
    @SerializedName("transaction_date") val transactionDate: String,
)

data class GoalRequest(
    @SerializedName("title") val title: String,
    @SerializedName("target_amount") val targetAmount: String,
    @SerializedName("deadline") val deadline: String?,
)

data class ContributionRequest(
    @SerializedName("account_id") val accountId: Long,
    @SerializedName("amount") val amount: String,
)

data class ContributionResponse(
    @SerializedName("goal") val goal: GoalDto?,
    @SerializedName("transaction") val transaction: TransactionDto?,
)
