package com.finaxis.finaxis_mobile

import android.content.Intent
import android.os.Bundle
import android.widget.PopupMenu
import androidx.appcompat.app.AppCompatActivity
import androidx.core.view.ViewCompat
import androidx.core.view.WindowInsetsCompat
import androidx.core.view.updatePadding
import androidx.fragment.app.Fragment
import androidx.lifecycle.Lifecycle
import androidx.lifecycle.lifecycleScope
import androidx.lifecycle.repeatOnLifecycle
import com.finaxis.finaxis_mobile.data.remote.SessionEvents
import com.finaxis.finaxis_mobile.databinding.ActivityMainBinding
import com.finaxis.finaxis_mobile.ui.dashboard.DashboardFragment
import com.finaxis.finaxis_mobile.ui.transactions.TransactionsFragment
import com.finaxis.finaxis_mobile.ui.accounts.AccountsFragment
import com.finaxis.finaxis_mobile.ui.goals.GoalsFragment
import com.finaxis.finaxis_mobile.ui.categories.CategoriesFragment
import com.finaxis.finaxis_mobile.ui.auth.LoginActivity
import com.finaxis.finaxis_mobile.ui.settings.SettingsFragment
import com.finaxis.finaxis_mobile.util.enableFinaxisEdgeToEdge
import kotlinx.coroutines.launch

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding

    // Evita abrir o login duas vezes (ex.: clicou em Sair e o token já tinha vencido).
    private var leaving = false

    override fun onCreate(savedInstanceState: Bundle?) {
        enableFinaxisEdgeToEdge()
        super.onCreate(savedInstanceState)

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)
        applySystemBarsPadding()

        binding.navDashboard.setOnClickListener {
            showScreen(DashboardFragment(), "Início")
        }

        binding.navTransactions.setOnClickListener {
            showScreen(TransactionsFragment(), "Transações")
        }

        binding.navAccounts.setOnClickListener {
            showScreen(AccountsFragment(), "Contas")
        }

        binding.navGoals.setOnClickListener {
            showScreen(GoalsFragment(), "Metas")
        }

        binding.btnMore.setOnClickListener {
            showPopupMenu()
        }

        if (savedInstanceState == null) {
            showScreen(DashboardFragment(), "Início")
        }

        // Se qualquer chamada à API responder 401 (token vencido), volta para o login.
        lifecycleScope.launch {
            repeatOnLifecycle(Lifecycle.State.STARTED) {
                SessionEvents.expired.collect { goToLogin() }
            }
        }
    }

    /**
     * No Android 15+ o app desenha a tela inteira, inclusive atrás da barra de status (em cima)
     * e da barra de navegação (embaixo). "Insets" são os tamanhos dessas barras.
     * Aqui empurramos o cabeçalho para baixo e o menu inferior para cima na medida certa.
     */
    private fun applySystemBarsPadding() {
        val headerTop = binding.headerBar.paddingTop

        ViewCompat.setOnApplyWindowInsetsListener(binding.root) { _, insets ->
            val bars = insets.getInsets(
                WindowInsetsCompat.Type.systemBars() or WindowInsetsCompat.Type.displayCutout()
            )
            binding.headerBar.updatePadding(top = headerTop + bars.top)
            binding.bottomNavBar.updatePadding(bottom = bars.bottom)
            // Celular deitado: as barras podem ficar nas laterais
            binding.root.updatePadding(left = bars.left, right = bars.right)
            insets
        }
    }

    private fun logout() {
        if (leaving) return
        leaving = true
        lifecycleScope.launch {
            (application as FinaxisApp).authRepository.logout()
            goToLogin()
        }
    }

    private fun goToLogin() {
        if (isFinishing) return
        val intent = Intent(this, LoginActivity::class.java)
            .addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TASK)
        startActivity(intent)
        finish()
    }

    private fun showScreen(fragment: Fragment, title: String) {
        supportFragmentManager.beginTransaction()
            .replace(R.id.fragmentContainer, fragment)
            .commit()

        binding.txtPageTitle.text = title
    }

    private fun showPopupMenu() {
        val popup = PopupMenu(this, binding.btnMore)
        popup.menuInflater.inflate(R.menu.main_menu, popup.menu)

        popup.setOnMenuItemClickListener { item ->
            when (item.itemId) {
                R.id.menuCategories -> {
                    showScreen(CategoriesFragment(), "Categorias")
                    true
                }

                R.id.menuSettings -> {
                    showScreen(SettingsFragment(), "Configurações")
                    true
                }

                R.id.menuLogout -> {
                    logout()
                    true
                }

                else -> false
            }
        }

        popup.show()
    }
}
