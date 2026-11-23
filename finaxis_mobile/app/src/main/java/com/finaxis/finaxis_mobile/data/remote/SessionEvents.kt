package com.finaxis.finaxis_mobile.data.remote

import kotlinx.coroutines.flow.MutableSharedFlow
import kotlinx.coroutines.flow.SharedFlow
import kotlinx.coroutines.flow.asSharedFlow

/**
 * "Canal" que avisa as telas quando a sessão expira (a API respondeu 401).
 * A MainActivity escuta isso e volta para o login.
 */
object SessionEvents {

    private val _expired = MutableSharedFlow<Unit>(extraBufferCapacity = 1)
    val expired: SharedFlow<Unit> = _expired.asSharedFlow()

    fun notifyExpired() {
        _expired.tryEmit(Unit)
    }
}
