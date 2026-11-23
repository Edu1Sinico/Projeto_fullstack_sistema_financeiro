package com.finaxis.finaxis_mobile.util

/** Traduz os códigos da API para o texto da tela (e vice-versa pelo índice da lista). */
object Labels {

    const val INCOME = "income"
    const val EXPENSE = "expense"
    const val ACTIVE = "active"
    const val INACTIVE = "inactive"

    /** Mesma ordem nas duas listas: o índice escolhido no menu vira o código da API. */
    val accountTypeCodes = listOf("checking", "savings", "wallet")
    val accountTypeNames = listOf("Conta corrente", "Poupança", "Carteira")

    fun accountType(code: String?): String =
        accountTypeNames.getOrNull(accountTypeCodes.indexOf(code)) ?: code.orEmpty()

    fun status(code: String?): String = if (code == INACTIVE) "Inativa" else "Ativa"
}
