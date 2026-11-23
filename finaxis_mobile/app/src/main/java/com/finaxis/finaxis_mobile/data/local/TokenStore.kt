package com.finaxis.finaxis_mobile.data.local

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.Preferences
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map

// Cria um único arquivo "finaxis_session" no armazenamento interno do app.
private val Context.sessionDataStore: DataStore<Preferences> by preferencesDataStore(name = "finaxis_session")

/**
 * Guarda o token JWT e o nome do usuário logado usando o DataStore.
 * Os dados continuam salvos mesmo se o app for fechado.
 */
class TokenStore(context: Context) {

    private val dataStore = context.applicationContext.sessionDataStore

    /** Emite o token atual (ou null quando ninguém está logado). */
    val token: Flow<String?> = dataStore.data.map { it[KEY_TOKEN] }

    /** Emite o nome do usuário logado (ou null). */
    val userName: Flow<String?> = dataStore.data.map { it[KEY_USER_NAME] }

    /** Lê o token uma vez (usado pelo interceptor do OkHttp). */
    suspend fun currentToken(): String? = token.first()

    suspend fun saveSession(token: String, userName: String?) {
        dataStore.edit { prefs ->
            prefs[KEY_TOKEN] = token
            if (userName != null) prefs[KEY_USER_NAME] = userName else prefs.remove(KEY_USER_NAME)
        }
    }

    suspend fun clear() {
        dataStore.edit { it.clear() }
    }

    private companion object {
        val KEY_TOKEN = stringPreferencesKey("access_token")
        val KEY_USER_NAME = stringPreferencesKey("user_name")
    }
}
