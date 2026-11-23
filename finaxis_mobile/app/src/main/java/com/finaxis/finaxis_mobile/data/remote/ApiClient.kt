package com.finaxis.finaxis_mobile.data.remote

import com.finaxis.finaxis_mobile.BuildConfig
import com.finaxis.finaxis_mobile.data.local.TokenStore
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.util.concurrent.TimeUnit

/** Monta o Retrofit uma vez só. Quem chama é a FinaxisApp. */
object ApiClient {

    fun create(tokenStore: TokenStore): ApiService {
        val logging = HttpLoggingInterceptor().apply {
            // Mostra as requisições no Logcat (filtre por "okhttp") só no build de debug.
            level = if (BuildConfig.DEBUG) HttpLoggingInterceptor.Level.BODY
            else HttpLoggingInterceptor.Level.NONE
        }

        val okHttp = OkHttpClient.Builder()
            .addInterceptor(AuthInterceptor(tokenStore))
            .addInterceptor(logging)
            // O plano gratuito do Render "dorme" depois de 15 min sem uso e
            // leva até ~1 minuto para acordar; por isso os tempos são maiores.
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(70, TimeUnit.SECONDS)
            .build()

        return Retrofit.Builder()
            .baseUrl(normalizeBaseUrl(BuildConfig.API_BASE_URL))
            .client(okHttp)
            .addConverterFactory(GsonConverterFactory.create())
            .build()
            .create(ApiService::class.java)
    }

    /** O Retrofit exige que a URL base termine com "/". */
    private fun normalizeBaseUrl(url: String): String =
        if (url.endsWith("/")) url else "$url/"
}
