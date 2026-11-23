package com.finaxis.finaxis_mobile.ui.accounts

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.finaxis.finaxis_mobile.data.remote.dto.AccountDto
import com.finaxis.finaxis_mobile.databinding.ItemAccountBinding
import com.finaxis.finaxis_mobile.util.Labels
import com.finaxis.finaxis_mobile.util.Money

class AccountAdapter(
    private val onClick: (AccountDto) -> Unit,
) : ListAdapter<AccountDto, AccountAdapter.ViewHolder>(Diff) {

    class ViewHolder(val binding: ItemAccountBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder =
        ViewHolder(ItemAccountBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val account = getItem(position)
        with(holder.binding) {
            txtName.text = account.name.orEmpty()
            val inactive = account.status == Labels.INACTIVE
            txtType.text = Labels.accountType(account.type) + if (inactive) " • Inativa" else ""
            txtBalance.text = Money.format(account.balance)
            root.alpha = if (inactive) 0.55f else 1f // conta inativa fica "apagada"
            root.setOnClickListener { onClick(account) }
        }
    }

    private object Diff : DiffUtil.ItemCallback<AccountDto>() {
        override fun areItemsTheSame(a: AccountDto, b: AccountDto) = a.id == b.id
        override fun areContentsTheSame(a: AccountDto, b: AccountDto) = a == b
    }
}
