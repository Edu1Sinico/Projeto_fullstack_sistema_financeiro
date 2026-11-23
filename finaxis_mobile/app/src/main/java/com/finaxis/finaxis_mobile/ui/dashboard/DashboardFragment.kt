package com.finaxis.finaxis_mobile.ui.dashboard

import android.os.Bundle
import android.view.View
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import com.finaxis.finaxis_mobile.FinaxisApp
import com.finaxis.finaxis_mobile.R
import com.finaxis.finaxis_mobile.data.remote.ApiResult
import com.finaxis.finaxis_mobile.data.remote.dto.DashboardDto
import com.finaxis.finaxis_mobile.databinding.FragmentDashboardBinding
import com.finaxis.finaxis_mobile.databinding.ItemGoalBinding
import com.finaxis.finaxis_mobile.databinding.ItemTransactionBinding
import com.finaxis.finaxis_mobile.ui.goals.bind
import com.finaxis.finaxis_mobile.ui.transactions.bind
import com.finaxis.finaxis_mobile.util.Money
import com.finaxis.finaxis_mobile.util.financeRepository
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.launch
import java.math.BigDecimal

/** Tela Início: saldo, receitas x despesas, metas e últimas transações (GET dashboard). */
class DashboardFragment : Fragment(R.layout.fragment_dashboard) {

    // O binding só existe enquanto a tela está visível (entre onViewCreated e onDestroyView).
    private var _binding: FragmentDashboardBinding? = null
    private val binding get() = _binding!!

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        _binding = FragmentDashboardBinding.bind(view)

        binding.swipeRefresh.setColorSchemeResources(R.color.finaxis_orange)
        binding.swipeRefresh.setOnRefreshListener { load() }

        showGreeting()
        load()
    }

    private fun showGreeting() {
        viewLifecycleOwner.lifecycleScope.launch {
            val fullName = (requireActivity().application as FinaxisApp).tokenStore.userName.first()
            val firstName = fullName?.trim()?.substringBefore(' ')
            binding.txtGreeting.text = if (firstName.isNullOrBlank()) "Olá! 👋" else "Olá, $firstName! 👋"
        }
    }

    private fun load() {
        binding.swipeRefresh.isRefreshing = true
        viewLifecycleOwner.lifecycleScope.launch {
            val result = financeRepository().dashboard()
            binding.swipeRefresh.isRefreshing = false
            when (result) {
                is ApiResult.Success -> {
                    binding.txtError.isVisible = false
                    render(result.data)
                }
                is ApiResult.HttpError -> showError(result.message)
                is ApiResult.NetworkError -> showError(result.message)
            }
        }
    }

    private fun showError(message: String) {
        binding.txtError.text = message
        binding.txtError.isVisible = true
    }

    private fun render(data: DashboardDto) {
        binding.txtTotalBalance.text = Money.format(data.balance)

        val accounts = data.accounts.orEmpty().size
        binding.txtAccountsCount.text = when (accounts) {
            0 -> "Nenhuma conta ativa"
            1 -> "Somando 1 conta ativa"
            else -> "Somando $accounts contas ativas"
        }

        binding.txtIncome.text = Money.format(data.income)
        binding.txtExpenses.text = Money.format(data.expenses)

        val result = Money.parse(data.result)
        val word = if (result >= BigDecimal.ZERO) "positivo" else "negativo"
        binding.txtResult.text = "Resultado $word: ${Money.format(result)} (receitas − despesas)"

        // Metas que ainda não foram concluídas (no máximo 3)
        val goals = data.goals.orEmpty().filter { it.completed != true }.take(3)
        binding.txtGoalsTitle.isVisible = goals.isNotEmpty()
        binding.goalsContainer.removeAllViews()
        goals.forEach { goal ->
            val row = ItemGoalBinding.inflate(layoutInflater, binding.goalsContainer, false)
            row.bind(goal)
            binding.goalsContainer.addView(row.root)
        }

        // Transações recentes (a API manda até 10)
        val recent = data.recentTransactions.orEmpty()
        binding.txtEmptyRecent.isVisible = recent.isEmpty()
        binding.recentContainer.removeAllViews()
        recent.forEach { transaction ->
            val row = ItemTransactionBinding.inflate(layoutInflater, binding.recentContainer, false)
            row.bind(transaction)
            binding.recentContainer.addView(row.root)
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
