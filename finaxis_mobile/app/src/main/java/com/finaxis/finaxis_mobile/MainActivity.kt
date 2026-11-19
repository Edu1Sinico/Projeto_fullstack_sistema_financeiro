package com.finaxis.finaxis_mobile

import android.os.Bundle
import android.widget.PopupMenu
import androidx.appcompat.app.AppCompatActivity
import androidx.fragment.app.Fragment
import com.finaxis.finaxis_mobile.databinding.ActivityMainBinding
import com.finaxis.finaxis_mobile.ui.dashboard.DashboardFragment
import com.finaxis.finaxis_mobile.ui.transactions.TransactionsFragment
import com.finaxis.finaxis_mobile.ui.accounts.AccountsFragment
import com.finaxis.finaxis_mobile.ui.goals.GoalsFragment
import com.finaxis.finaxis_mobile.ui.categories.CategoriesFragment
import com.finaxis.finaxis_mobile.ui.settings.SettingsFragment

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

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
                    // Implementaremos após a autenticação.
                    true
                }

                else -> false
            }
        }

        popup.show()
    }
}
