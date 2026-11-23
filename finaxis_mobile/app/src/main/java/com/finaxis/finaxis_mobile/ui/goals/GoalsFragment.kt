package com.finaxis.finaxis_mobile.ui.goals

import android.os.Bundle
import android.view.View
import android.widget.Toast
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import com.finaxis.finaxis_mobile.R
import com.finaxis.finaxis_mobile.data.remote.ApiResult
import com.finaxis.finaxis_mobile.data.remote.dto.AccountDto
import com.finaxis.finaxis_mobile.data.remote.dto.ContributionRequest
import com.finaxis.finaxis_mobile.data.remote.dto.GoalDto
import com.finaxis.finaxis_mobile.data.remote.dto.GoalRequest
import com.finaxis.finaxis_mobile.data.remote.errorText
import com.finaxis.finaxis_mobile.databinding.DialogContributionBinding
import com.finaxis.finaxis_mobile.databinding.DialogGoalBinding
import com.finaxis.finaxis_mobile.databinding.FragmentGoalsBinding
import com.finaxis.finaxis_mobile.util.Dates
import com.finaxis.finaxis_mobile.util.Labels
import com.finaxis.finaxis_mobile.util.Money
import com.finaxis.finaxis_mobile.util.confirm
import com.finaxis.finaxis_mobile.util.financeRepository
import com.finaxis.finaxis_mobile.util.setOptions
import com.finaxis.finaxis_mobile.util.showFormDialog
import com.google.android.material.datepicker.MaterialDatePicker
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import kotlinx.coroutines.launch
import java.math.BigDecimal
import java.time.LocalDate

/**
 * Tela Metas: listar, criar, editar, excluir, contribuir e remover contribuições.
 * Contribuir tira o dinheiro de uma conta (vira uma despesa "Contribuição: <meta>").
 */
class GoalsFragment : Fragment(R.layout.fragment_goals) {

    private var _binding: FragmentGoalsBinding? = null
    private val binding get() = _binding!!

    private val adapter = GoalAdapter(onClick = ::showGoalActions)

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        _binding = FragmentGoalsBinding.bind(view)

        binding.recyclerView.layoutManager = LinearLayoutManager(requireContext())
        binding.recyclerView.adapter = adapter
        binding.swipeRefresh.setColorSchemeResources(R.color.finaxis_orange)
        binding.swipeRefresh.setOnRefreshListener { load() }
        binding.fabAdd.setOnClickListener { showGoalForm(null) }

        load()
    }

    private fun load() {
        binding.swipeRefresh.isRefreshing = true
        viewLifecycleOwner.lifecycleScope.launch {
            val result = financeRepository().goals()
            binding.swipeRefresh.isRefreshing = false
            if (result is ApiResult.Success) {
                binding.txtError.isVisible = false
                adapter.submitList(result.data)
                binding.txtEmpty.isVisible = result.data.isEmpty()
            } else {
                binding.txtError.text = result.errorText()
                binding.txtError.isVisible = true
            }
        }
    }

    private fun toast(message: String?) {
        Toast.makeText(requireContext(), message, Toast.LENGTH_LONG).show()
    }

    /** Menu ao tocar numa meta. */
    private fun showGoalActions(goal: GoalDto) {
        val actions = buildList {
            if (goal.completed != true) add("Contribuir")
            add("Ver contribuições")
            add("Editar")
            add("Excluir")
        }
        MaterialAlertDialogBuilder(requireContext())
            .setTitle(goal.title)
            .setItems(actions.toTypedArray()) { _, which ->
                when (actions[which]) {
                    "Contribuir" -> showContributionForm(goal)
                    "Ver contribuições" -> showContributions(goal)
                    "Editar" -> showGoalForm(goal)
                    "Excluir" -> confirm(
                        requireContext(),
                        "Excluir meta?",
                        "O dinheiro já guardado nesta meta volta para as contas de origem.",
                    ) { deleteGoal(goal) }
                }
            }
            .show()
    }

    // ---------- Criar / editar ----------

    private fun showGoalForm(goal: GoalDto?) {
        val form = DialogGoalBinding.inflate(layoutInflater)
        val editing = goal != null
        var deadline: LocalDate? = Dates.parseApi(goal?.deadline)

        form.edtTitle.setText(goal?.title.orEmpty())
        form.edtTarget.setText(goal?.let { Money.toInput(it.targetAmount) }.orEmpty())
        form.edtDeadline.setText(deadline?.let { Dates.format(it) }.orEmpty())

        form.edtDeadline.setOnClickListener {
            val picker = MaterialDatePicker.Builder.datePicker()
                .setTitleText("Prazo da meta")
                .setSelection(Dates.toPickerMillis(deadline ?: LocalDate.now().plusMonths(1)))
                .build()
            picker.addOnPositiveButtonClickListener { millis ->
                deadline = Dates.fromPickerMillis(millis)
                form.edtDeadline.setText(Dates.format(deadline!!))
            }
            picker.show(childFragmentManager, "deadlinePicker")
        }
        // Ícone "x" no fim do campo para tirar o prazo
        form.tilDeadline.setEndIconMode(com.google.android.material.textfield.TextInputLayout.END_ICON_CLEAR_TEXT)
        form.tilDeadline.setEndIconOnClickListener {
            deadline = null
            form.edtDeadline.setText("")
        }

        showFormDialog(
            context = requireContext(),
            title = if (editing) "Editar meta" else "Nova meta",
            view = form.root,
        ) { dialog ->
            form.tilTitle.error = null
            form.tilTarget.error = null
            form.txtError.isVisible = false

            val title = form.edtTitle.text?.toString().orEmpty().trim()
            val target = Money.parseUserInput(form.edtTarget.text?.toString())
            var valid = true
            if (title.length < 3) { form.tilTitle.error = "Use pelo menos 3 letras."; valid = false }
            if (target == null || target.signum() <= 0) { form.tilTarget.error = "Informe um valor maior que zero."; valid = false }
            if (!valid) return@showFormDialog

            val body = GoalRequest(title, Money.toApi(target!!), deadline?.let { Dates.toApi(it) })
            viewLifecycleOwner.lifecycleScope.launch {
                val result = if (editing) financeRepository().updateGoal(goal!!.id ?: 0L, body)
                else financeRepository().createGoal(body)
                if (result is ApiResult.Success) {
                    dialog.dismiss()
                    toast(if (editing) "Meta atualizada." else "Meta criada.")
                    load()
                } else {
                    if (result is ApiResult.HttpError) {
                        form.tilTitle.error = result.fieldErrors["title"]?.firstOrNull()
                        // Ex.: "O objetivo não pode ser menor que o valor já contribuído."
                        form.tilTarget.error = result.fieldErrors["target_amount"]?.firstOrNull()
                    }
                    form.txtError.text = result.errorText()
                    form.txtError.isVisible = true
                }
            }
        }
    }

    private fun deleteGoal(goal: GoalDto) {
        viewLifecycleOwner.lifecycleScope.launch {
            val result = financeRepository().deleteGoal(goal.id ?: 0L)
            if (result is ApiResult.Success) {
                toast("Meta excluída.")
                load()
            } else {
                toast(result.errorText())
            }
        }
    }

    // ---------- Contribuir ----------

    private fun showContributionForm(goal: GoalDto) {
        viewLifecycleOwner.lifecycleScope.launch {
            val accountsResult = financeRepository().accounts()
            if (accountsResult !is ApiResult.Success) {
                toast(accountsResult.errorText())
                return@launch
            }
            val accounts = accountsResult.data.filter { it.status != Labels.INACTIVE }
            if (accounts.isEmpty()) {
                toast("Cadastre uma conta ativa primeiro (aba Contas).")
                return@launch
            }
            buildContributionForm(goal, accounts)
        }
    }

    private fun buildContributionForm(goal: GoalDto, accounts: List<AccountDto>) {
        val form = DialogContributionBinding.inflate(layoutInflater)
        val remaining = (Money.parse(goal.targetAmount) - Money.parse(goal.currentAmount)).max(BigDecimal.ZERO)
        form.txtRemaining.text = "Faltam ${Money.format(remaining)} para concluir a meta."

        var account: AccountDto? = null
        form.dropAccount.setOptions(
            accounts.map { "${it.name} (${Money.format(it.balance)})" },
            if (accounts.size == 1) 0 else -1,
        ) { account = accounts[it] }

        showFormDialog(
            context = requireContext(),
            title = "Contribuir: ${goal.title}",
            view = form.root,
            positiveText = "Contribuir",
        ) { dialog ->
            form.tilAccount.error = null
            form.tilAmount.error = null
            form.txtError.isVisible = false

            val amount = Money.parseUserInput(form.edtAmount.text?.toString())
            var valid = true
            if (account == null) { form.tilAccount.error = "Escolha a conta."; valid = false }
            val chosen = account
            if (amount == null || amount.signum() <= 0) {
                form.tilAmount.error = "Informe um valor maior que zero."
                valid = false
            } else if (amount > remaining) {
                form.tilAmount.error = "Máximo: ${Money.format(remaining)}"
                valid = false
            } else if (chosen != null && amount > Money.parse(chosen.balance)) {
                form.tilAmount.error = "Saldo insuficiente nessa conta."
                valid = false
            }
            if (!valid) return@showFormDialog

            viewLifecycleOwner.lifecycleScope.launch {
                val result = financeRepository().contribute(
                    goal.id ?: 0L,
                    ContributionRequest(account!!.id ?: 0L, Money.toApi(amount!!)),
                )
                if (result is ApiResult.Success) {
                    dialog.dismiss()
                    toast(if (result.data.goal?.completed == true) "Meta concluída! 🎉" else "Contribuição registrada.")
                    load()
                } else {
                    if (result is ApiResult.HttpError) {
                        form.tilAmount.error = result.fieldErrors["amount"]?.firstOrNull()
                        form.tilAccount.error = result.fieldErrors["account_id"]?.firstOrNull()
                    }
                    form.txtError.text = result.errorText()
                    form.txtError.isVisible = true
                }
            }
        }
    }

    // ---------- Ver / remover contribuições ----------

    private fun showContributions(goal: GoalDto) {
        viewLifecycleOwner.lifecycleScope.launch {
            val result = financeRepository().goal(goal.id ?: 0L)
            if (result !is ApiResult.Success) {
                toast(result.errorText())
                return@launch
            }
            val contributions = result.data.transactions.orEmpty()
            if (contributions.isEmpty()) {
                toast("Nenhuma contribuição nesta meta ainda.")
                return@launch
            }
            val lines = contributions.map {
                "${Dates.format(it.transactionDate)} • ${Money.format(it.contributedAmount ?: it.amount)}"
            }
            MaterialAlertDialogBuilder(requireContext())
                .setTitle("Contribuições (toque para remover)")
                .setItems(lines.toTypedArray()) { _, which ->
                    val item = contributions[which]
                    confirm(
                        requireContext(),
                        "Remover contribuição?",
                        "${Money.format(item.contributedAmount ?: item.amount)} volta para a conta ${item.accountName ?: "de origem"}.",
                        confirmText = "Remover",
                    ) { removeContribution(goal, item.id ?: 0L) }
                }
                .setNegativeButton("Fechar", null)
                .show()
        }
    }

    private fun removeContribution(goal: GoalDto, transactionId: Long) {
        viewLifecycleOwner.lifecycleScope.launch {
            val result = financeRepository().removeContribution(goal.id ?: 0L, transactionId)
            if (result is ApiResult.Success) {
                toast("Contribuição removida.")
                load()
            } else {
                toast(result.errorText())
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
