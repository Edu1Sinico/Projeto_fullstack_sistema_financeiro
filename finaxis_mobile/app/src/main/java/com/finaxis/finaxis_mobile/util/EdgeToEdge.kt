package com.finaxis.finaxis_mobile.util

import android.graphics.Color
import androidx.activity.ComponentActivity
import androidx.activity.SystemBarStyle
import androidx.activity.enableEdgeToEdge

/**
 * Liga o modo "tela inteira" (edge-to-edge) do jeito que o Android 15+ já exige,
 * com barras transparentes e ícones BRANCOS (relógio, bateria, botões), porque o
 * cabeçalho e o menu de baixo do Finaxis são escuros.
 *
 * Chame antes do super.onCreate() da Activity. Depois, cada tela precisa tratar os
 * insets (ver MainActivity.applySystemBarsPadding).
 */
fun ComponentActivity.enableFinaxisEdgeToEdge() {
    enableEdgeToEdge(
        statusBarStyle = SystemBarStyle.dark(Color.TRANSPARENT),
        navigationBarStyle = SystemBarStyle.dark(Color.TRANSPARENT),
    )
}
