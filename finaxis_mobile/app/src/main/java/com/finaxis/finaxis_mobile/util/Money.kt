package com.finaxis.finaxis_mobile.util

import java.math.BigDecimal
import java.math.RoundingMode
import java.text.NumberFormat
import java.util.Locale

/** Conversões de dinheiro. Sempre BigDecimal: Double erra centavos (0.1 + 0.2 = 0.30000000000000004). */
object Money {

    private val brazil: Locale = Locale.forLanguageTag("pt-BR")

    /** "1234.5" (texto da API) -> 1234.50. Texto inválido vira zero. */
    fun parse(raw: String?): BigDecimal =
        raw?.trim()?.toBigDecimalOrNull()?.setScale(2, RoundingMode.HALF_UP) ?: BigDecimal.ZERO

    /** 1234.5 -> "R$ 1.234,50" */
    fun format(value: BigDecimal): String = NumberFormat.getCurrencyInstance(brazil).format(value)

    fun format(raw: String?): String = format(parse(raw))

    /**
     * Lê o que o usuário digitou: "1.234,56", "1234,56", "1234.56", "R$ 50".
     * Devolve null se não for um número válido ou tiver mais de 2 casas decimais.
     */
    fun parseUserInput(text: String?): BigDecimal? {
        var clean = text.orEmpty().replace("R$", "").replace(" ", "").replace(" ", "").trim()
        if (clean.isEmpty()) return null
        if (clean.contains(',')) {
            // Formato brasileiro: ponto separa milhar, vírgula separa centavos.
            clean = clean.replace(".", "").replace(',', '.')
        }
        val value = clean.toBigDecimalOrNull() ?: return null
        if (value.scale() > 2) return null
        return value.setScale(2, RoundingMode.HALF_UP)
    }

    /** 1234.5 -> "1234.50" (formato que a API espera) */
    fun toApi(value: BigDecimal): String = value.setScale(2, RoundingMode.HALF_UP).toPlainString()

    /** 1234.5 -> "1234,50" (para preencher um campo de edição) */
    fun toInput(raw: String?): String = toApi(parse(raw)).replace('.', ',')
}
