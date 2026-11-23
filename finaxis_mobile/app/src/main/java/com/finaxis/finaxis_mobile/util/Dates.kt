package com.finaxis.finaxis_mobile.util

import java.time.Instant
import java.time.LocalDate
import java.time.ZoneOffset
import java.time.format.DateTimeFormatter

/** A API usa "2026-11-19". Na tela mostramos "19/11/2026". */
object Dates {

    private val display = DateTimeFormatter.ofPattern("dd/MM/yyyy")

    fun parseApi(raw: String?): LocalDate? = try {
        raw?.take(10)?.let { LocalDate.parse(it) } // take(10) ignora hora, se vier
    } catch (e: Exception) {
        null
    }

    fun format(raw: String?): String = parseApi(raw)?.format(display) ?: raw.orEmpty()

    fun format(date: LocalDate): String = date.format(display)

    fun toApi(date: LocalDate): String = date.toString()

    /** O MaterialDatePicker trabalha com milissegundos em UTC. */
    fun toPickerMillis(date: LocalDate): Long = date.atStartOfDay(ZoneOffset.UTC).toInstant().toEpochMilli()

    fun fromPickerMillis(millis: Long): LocalDate = Instant.ofEpochMilli(millis).atZone(ZoneOffset.UTC).toLocalDate()
}
