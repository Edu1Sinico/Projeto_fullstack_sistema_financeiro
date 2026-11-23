package com.finaxis.finaxis_mobile.ui.transactions

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
import com.finaxis.finaxis_mobile.data.remote.dto.CategoryDto
import com.finaxis.finaxis_mobile.data.remote.dto.TransactionDto
import com.finaxis.finaxis_mobile.data.remote.dto.TransactionRequest
import com.finaxis.finaxis_mobile.data.remote.errorText
import com.finaxis.finaxis_mobile.databinding.DialogTransactionBinding
import com.finaxis.finaxis_mobile.databinding.FragmentTransactionsBinding
import com.finaxis.finaxis_mobile.util.Dates
import com.finaxis.finaxis_mobile.util.Labels
import com.finaxis.finaxis_mobile.util.Money
import com.finaxis.finaxis_mobile.util.confirm
import com.finaxis.finaxis_mobile.util.financeRepository
import com.finaxis.finaxis_mobile.util.setOptions
import com.finaxis.finaxis_mobile.util.showFormDialog
import com.google.android.material.datepicker.MaterialDatePicker
import kotlinx.coroutines.async
import kotlinx.coroutines.launch
import java.time.LocalDate

/** Tela Transações: listar (com filtro), criar, editar e excluir. */
class TransactionsFragment : Fragment(R.layout.fragment_transactions) {

    private var _binding: FragmentTransactionsBinding? = null
    private val binding get() = _binding!!

    private val adapter = TransactionAdapter(onClick = ::openTransaction)

    /** null = todas, "income" ou "expense" */
    private var typeFilter: String? = null

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        _binding = FragmentTransactionsBinding.bind(view)

        binding.recyclerView.layoutManager = LinearLayoutManager(requireContext())
        binding.recyclerView.adapter = adapter
        binding.swipeRefresh.setColorSchemeResources(R.color.finaxis_orange)
        binding.swipeRefresh.setOnRefreshListener { load() }
        binding.fabAdd.setOnClickListener { showTransactionForm(null) }

        binding.chipGroupType.setOnCheckedStateChangeListener { _, checkedIds ->
            typeFilter = when (checkedIds.firstOrNull()) {
                R.id.chipIncome -> Labels.INCOME
                R.id.chipExpense -> Labels.EXPENSE
                else -> null
            }
            load()
        }

        load()
    }

    private fun load() {
        binding.swipeRefresh.isRefreshing = true
        viewLifecycleOwner.lifecycleScope.launch {
            val result = financeRepository().transactions(typeFilter)
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

    private fun openTransaction(transaction: TransactionDto) {
        // Contribuições de metas só podem ser mexidas pela tela de Metas (regra da API).
        if ((transaction.goalsCount ?: 0) > 0) {
            Toast.makeText(
                requireContext(),
                "Esta transação é uma contribuição de meta. Altere ou remova pela aba Metas.",
                Toast.LENGTH_LONG,
            ).show()
            return
        }
        showTransactionForm(transaction)
    }

    /**
     * Abre o formulário. Antes, busca as contas e categorias (as duas ao mesmo tempo, com async)
     * para preencher as listas suspensas.
     */
    private fun showTransactionForm(transaction: TransactionDto?) {
        viewLifecycleOwner.lifecycleScope.launch {
            val accountsCall = async { financeRepository().accounts() }
            val categoriesCall = async { financeRepository().categories() }
            val accountsResult = accountsCall.await()
            val categoriesResult = categoriesCall.await()

            if (accountsResult !is ApiResult.Success || categoriesResult !is ApiResult.Success) {
                val error = accountsResult.errorText() ?: categoriesResult.errorText()
                Toast.makeText(requireContext(), error, Toast.LENGTH_LONG).show()
                return@launch
            }

            // Só contas e categorias ativas podem receber transações.
            val accounts = accountsResult.data.filter { it.status != Labels.INACTIVE }
            if (accounts.isEmpty()) {
                Toast.makeText(requireContext(), "Cadastre uma conta ativa primeiro (aba Contas).", Toast.LENGTH_LONG).show()
                return@launch
            }
            val categories = categoriesResult.data.filter { it.status != Labels.INACTIVE }

            buildForm(transaction, accounts, categories)
        }
    }

    private fun buildForm(transaction: TransactionDto?, accounts: List<AccountDto>, allCategories: List<CategoryDto>) {
        val form = DialogTransactionBinding.inflate(layoutInflater)
        val editing = transaction != null

        var type = transaction?.type ?: Labels.EXPENSE
        var date: LocalDate = Dates.parseApi(transaction?.transactionDate) ?: LocalDate.now()
        var account: AccountDto? = null
        var category: CategoryDto? = null
        var categories: List<CategoryDto> = emptyList()

        // Conta
        val accountIndex = accounts.indexOfFirst { it.id == transaction?.accountId }
            .let { if (it == -1 && accounts.size == 1) 0 else it }
        form.dropAccount.setOptions(
            accounts.map { "${it.name} (${Money.format(it.balance)})" },
            accountIndex,
        ) { account = accounts[it] }

        // Categoria: a lista muda conforme o tipo (receita/despesa)
        fun refreshCategories(keepId: Long?) {
            categories = allCategories.filter { it.type == type }
            category = null
            form.dropCategory.setOptions(
                categories.map { it.name.orEmpty() },
                categories.indexOfFirst { it.id == keepId },
            ) { category = categories[it] }
        }

        form.toggleType.check(if (type == Labels.INCOME) R.id.btnIncome else R.id.btnExpense)
        refreshCategories(transaction?.categoryId)
        form.toggleType.addOnButtonCheckedListener { _, checkedId, isChecked ->
            if (!isChecked) return@addOnButtonCheckedListener
            type = if (checkedId == R.id.btnIncome) Labels.INCOME else Labels.EXPENSE
            refreshCategories(null)
        }

        form.edtDescription.setText(transaction?.description.orEmpty())
        form.edtAmount.setText(transaction?.let { Money.toInput(it.amount) }.orEmpty())

        // Data: tocar no campo abre o calendário
        form.edtDate.setText(Dates.format(date))
        form.edtDate.setOnClickListener {
            val picker = MaterialDatePicker.Builder.datePicker()
                .setTitleText("Data da transação")
                .setSelection(Dates.toPickerMillis(date))
                .build()
            picker.addOnPositiveButtonClickListener { millis ->
                date = Dates.fromPickerMillis(millis)
                form.edtDate.setText(Dates.format(date))
            }
            picker.show(childFragmentManager, "datePicker")
        }

        showFormDialog(
            context = requireContext(),
            title = if (editing) "Editar transação" else "Nova transação",
            view = form.root,
            neutralText = if (editing) "Excluir" else null,
            onNeutral = { dialog ->
                confirm(requireContext(), "Excluir transação?", "O saldo da conta será ajustado de volta.") {
                    deleteTransaction(transaction!!) { dialog.dismiss() }
                }
            },
        ) { dialog ->
            listOf(form.tilDescription, form.tilAmount, form.tilAccount, form.tilCategory).forEach { it.error = null }
            form.txtError.isVisible = false

            val description = form.edtDescription.text?.toString().orEmpty().trim()
            val amount = Money.parseUserInput(form.edtAmount.text?.toString())
            var valid = true
            if (description.length < 3) { form.tilDescription.error = "Use pelo menos 3 letras."; valid = false }
            if (amount == null || amount.signum() <= 0) { form.tilAmount.error = "Informe um valor maior que zero. Ex.: 25,90"; valid = false }
            if (account == null) { form.tilAccount.error = "Escolha a conta."; valid = false }
            if (category == null) { form.tilCategory.error = "Escolha a categoria."; valid = false }
            if (!valid) return@showFormDialog

            val body = TransactionRequest(
                accountId = account!!.id ?: 0L,
                categoryId = category!!.id ?: 0L,
                description = description,
                amount = Money.toApi(amount!!),
                type = type,
                transactionDate = Dates.toApi(date),
            )

            viewLifecycleOwner.lifecycleScope.launch {
                val result = if (editing) financeRepository().updateTransaction(transaction!!.id ?: 0L, body)
                else financeRepository().createTransaction(body)

                if (result is ApiResult.Success) {
                    dialog.dismiss()
                    Toast.makeText(requireContext(), if (editing) "Transação atualizada." else "Transação salva.", Toast.LENGTH_SHORT).show()
                    load()
                } else {
                    if (result is ApiResult.HttpError) {
                        // Ex.: "Saldo insuficiente para realizar esta operação." vem no campo amount
                        form.tilDescription.error = result.fieldErrors["description"]?.firstOrNull()
                        form.tilAmount.error = result.fieldErrors["amount"]?.firstOrNull()
                        form.tilAccount.error = result.fieldErrors["account_id"]?.firstOrNull()
                        form.tilCategory.error = result.fieldErrors["category_id"]?.firstOrNull()
                    }
                    form.txtError.text = result.errorText()
                    form.txtError.isVisible = true
                }
            }
        }
    }

    private fun deleteTransaction(transaction: TransactionDto, onDone: () -> Unit) {
        viewLifecycleOwner.lifecycleScope.launch {
            val result = financeRepository().deleteTransaction(transaction.id ?: 0L)
            if (result is ApiResult.Success) {
                onDone()
                Toast.makeText(requireContext(), "Transação excluída.", Toast.LENGTH_SHORT).show()
                load()
            } else {
                // Ex.: "Saldo insuficiente" ao apagar uma receita que já foi gasta
                Toast.makeText(requireContext(), result.errorText(), Toast.LENGTH_LONG).show()
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
