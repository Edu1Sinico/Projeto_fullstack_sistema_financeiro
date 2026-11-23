package com.finaxis.finaxis_mobile.ui.transactions

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.finaxis.finaxis_mobile.R
import com.finaxis.finaxis_mobile.data.remote.dto.TransactionDto
import com.finaxis.finaxis_mobile.databinding.ItemTransactionBinding
import com.finaxis.finaxis_mobile.util.Dates
import com.finaxis.finaxis_mobile.util.Labels
import com.finaxis.finaxis_mobile.util.Money

/** Liga os dados de uma transação na linha item_transaction.xml. Usado aqui e no Início. */
fun ItemTransactionBinding.bind(t: TransactionDto) {
    txtDescription.text = t.description.orEmpty()
    txtDetails.text = listOfNotNull(t.categoryName, t.accountName, Dates.format(t.transactionDate))
        .filter { it.isNotBlank() }
        .joinToString(" • ")

    val income = t.type == Labels.INCOME
    val sign = if (income) "+ " else "− "
    txtAmount.text = sign + Money.format(t.amount)
    txtAmount.setTextColor(
        ContextCompat.getColor(root.context, if (income) R.color.finaxis_green else R.color.finaxis_red)
    )
}

/**
 * Adapter = a "ponte" entre a lista de dados e o RecyclerView.
 * O ListAdapter compara a lista nova com a antiga (DiffUtil) e só redesenha o que mudou.
 */
class TransactionAdapter(
    private val onClick: (TransactionDto) -> Unit,
) : ListAdapter<TransactionDto, TransactionAdapter.ViewHolder>(Diff) {

    class ViewHolder(val binding: ItemTransactionBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder =
        ViewHolder(ItemTransactionBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val item = getItem(position)
        holder.binding.bind(item)
        holder.binding.root.setOnClickListener { onClick(item) }
    }

    private object Diff : DiffUtil.ItemCallback<TransactionDto>() {
        override fun areItemsTheSame(a: TransactionDto, b: TransactionDto) = a.id == b.id
        override fun areContentsTheSame(a: TransactionDto, b: TransactionDto) = a == b
    }
}
