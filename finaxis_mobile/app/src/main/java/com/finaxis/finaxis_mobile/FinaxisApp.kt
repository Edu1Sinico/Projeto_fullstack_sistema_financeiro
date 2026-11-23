package com.finaxis.finaxis_mobile

import android.app.Application
import androidx.appcompat.app.AppCompatDelegate
import com.finaxis.finaxis_mobile.data.local.TokenStore
import com.finaxis.finaxis_mobile.data.remote.ApiClient
import com.finaxis.finaxis_mobile.data.remote.ApiService
import com.finaxis.finaxis_mobile.data.repository.AuthRepository
import com.finaxis.finaxis_mobile.data.repository.FinanceRepository

/**
 * Classe que o Android cria uma única vez, antes de qualquer tela.
 * Guardamos aqui os objetos que o app inteiro compartilha (token, Retrofit, repositórios).
 *
 * Numa tela, pegue assim:  (application as FinaxisApp).authRepository
 */
class FinaxisApp : Application() {

    lateinit var tokenStore: TokenStore
        private set

    lateinit var api: ApiService
        private set

    lateinit var authRepository: AuthRepository
        private set

    lateinit var financeRepository: FinanceRepository
        private set

    override fun onCreate() {
        super.onCreate()
        // As telas foram desenhadas com fundo branco. Até termos um tema escuro de verdade,
        // o app fica sempre claro, mesmo com o modo escuro do celular ligado.
        AppCompatDelegate.setDefaultNightMode(AppCompatDelegate.MODE_NIGHT_NO)
        tokenStore = TokenStore(this)
        api = ApiClient.create(tokenStore)
        authRepository = AuthRepository(api, tokenStore)
        financeRepository = FinanceRepository(api)
    }
}
