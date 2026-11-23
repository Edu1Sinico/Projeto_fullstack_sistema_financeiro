package com.finaxis.finaxis_mobile.ui.accounts

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
import com.finaxis.finaxis_mobile.data.remote.dto.AccountRequest
import com.finaxis.finaxis_mobile.data.remote.errorText
import com.finaxis.finaxis_mobile.databinding.DialogAccountBinding
import com.finaxis.finaxis_mobile.databinding.FragmentAccountsBinding
import com.finaxis.finaxis_mobile.util.Labels
import com.finaxis.finaxis_mobile.util.Money
import com.finaxis.finaxis_mobile.util.confirm
import com.finaxis.finaxis_mobile.util.financeRepository
import com.finaxis.finaxis_mobile.util.setOptions
import com.finaxis.finaxis_mobile.util.showFormDialog
import kotlinx.coroutines.launch
import java.math.BigDecimal

/** Tela Contas: listar, criar, editar (nome, tipo, situação) e excluir. */
class AccountsFragment : Fragment(R.layout.fragment_accounts) {

    private var _binding: FragmentAccountsBinding? = null
    private val binding get() = _binding!!

    private val adapter = AccountAdapter(onClick = ::showAccountForm)

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        super.onViewCreated(view, savedInstanceState)
        _binding = FragmentAccountsBinding.bind(view)

        binding.recyclerView.layoutManager = LinearLayoutManager(requireContext())
        binding.recyclerView.adapter = adapter
        binding.swipeRefresh.setColorSchemeResources(R.color.finaxis_orange)
        binding.swipeRefresh.setOnRefreshListener { load() }
        binding.fabAdd.setOnClickListener { showAccountForm(null) }

        load()
    }

    private fun load() {
        binding.swipeRefresh.isRefreshing = true
        viewLifecycleOwner.lifecycleScope.launch {
            val result = financeRepository().accounts()
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

    /** account == null: cadastro. Senão: edição. */
    private fun showAccountForm(account: AccountDto?) {
        val form = DialogAccountBinding.inflate(layoutInflater)
        val editing = account != null

        var typeIndex = Labels.accountTypeCodes.indexOf(account?.type ?: "checking")
        form.dropType.setOptions(Labels.accountTypeNames, typeIndex) { typeIndex = it }

        val statusCodes = listOf(Labels.ACTIVE, Labels.INACTIVE)
        var statusIndex = statusCodes.indexOf(account?.status ?: Labels.ACTIVE)
        form.dropStatus.setOptions(listOf("Ativa", "Inativa"), statusIndex) { statusIndex = it }

        form.edtName.setText(account?.name.orEmpty())
        // O saldo só é informado no cadastro; na edição mostramos o atual, sem deixar mudar.
        if (editing) {
            form.edtBalance.setText(Money.format(account?.balance))
            form.edtBalance.isEnabled = false
            form.tilBalance.helperText = "Para mudar o saldo, lance uma transação."
        } else {
            form.tilStatus.isVisible = false // conta nova sempre nasce ativa
        }

        showFormDialog(
            context = requireContext(),
            title = if (editing) "Editar conta" else "Nova conta",
            view = form.root,
            neutralText = if (editing) "Excluir" else null,
            onNeutral = { dialog ->
                confirm(
                    requireContext(),
                    "Excluir conta?",
                    "A conta \"${account?.name}\" e TODAS as transações dela serão apagadas. Isso não pode ser desfeito.",
                ) {
                    deleteAccount(account!!) { dialog.dismiss() }
                }
            },
        ) { dialog ->
            form.tilName.error = null
            form.tilBalance.error = null
            form.txtError.isVisible = false

            val name = form.edtName.text?.toString().orEmpty().trim()
            var valid = true
            if (name.length < 3) {
                form.tilName.error = "Use pelo menos 3 letras."
                valid = false
            }
            var balance: BigDecimal? = BigDecimal.ZERO
            if (!editing) {
                val raw = form.edtBalance.text?.toString().orEmpty()
                balance = if (raw.isBlank()) BigDecimal.ZERO else Money.parseUserInput(raw)
                if (balance == null || balance < BigDecimal.ZERO) {
                    form.tilBalance.error = "Valor inválido. Ex.: 150,00"
                    valid = false
                }
            }
            if (!valid) return@showFormDialog

            val body = AccountRequest(
                name = name,
                type = Labels.accountTypeCodes[typeIndex.coerceAtLeast(0)],
                status = if (editing) statusCodes[statusIndex.coerceAtLeast(0)] else null,
                balance = if (editing) null else Money.toApi(balance!!),
            )

            viewLifecycleOwner.lifecycleScope.launch {
                val result = if (editing) financeRepository().updateAccount(account!!.id ?: 0L, body)
                else financeRepository().createAccount(body)

                if (result is ApiResult.Success) {
                    dialog.dismiss()
                    Toast.makeText(requireContext(), if (editing) "Conta atualizada." else "Conta criada.", Toast.LENGTH_SHORT).show()
                    load()
                } else {
                    if (result is ApiResult.HttpError) {
                        form.tilName.error = result.fieldErrors["name"]?.firstOrNull()
                        form.tilBalance.error = result.fieldErrors["balance"]?.firstOrNull()
                    }
                    form.txtError.text = result.errorText()
                    form.txtError.isVisible = true
                }
            }
        }
    }

    private fun deleteAccount(account: AccountDto, onDone: () -> Unit) {
        viewLifecycleOwner.lifecycleScope.launch {
            val result = financeRepository().deleteAccount(account.id ?: 0L)
            if (result is ApiResult.Success) {
                onDone()
                Toast.makeText(requireContext(), "Conta excluída.", Toast.LENGTH_SHORT).show()
                load()
            } else {
                Toast.makeText(requireContext(), result.errorText(), Toast.LENGTH_LONG).show()
            }
        }
    }

    override fun onDestroyView() {
        super.onDestroyView()
        _binding = null
    }
}
