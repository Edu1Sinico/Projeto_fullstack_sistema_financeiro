package com.finaxis.finaxis_mobile.ui.goals

import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.ListAdapter
import androidx.recyclerview.widget.RecyclerView
import com.finaxis.finaxis_mobile.data.remote.dto.GoalDto
import com.finaxis.finaxis_mobile.databinding.ItemGoalBinding
import com.finaxis.finaxis_mobile.util.Dates
import com.finaxis.finaxis_mobile.util.Money
import kotlin.math.roundToInt

/** Liga os dados de uma meta em item_goal.xml. Usado aqui e no Início. */
fun ItemGoalBinding.bind(goal: GoalDto) {
    val percent = (goal.progressPercentage ?: 0.0).coerceIn(0.0, 100.0)
    txtTitle.text = goal.title.orEmpty()
    txtPercent.text = if (goal.completed == true) "Concluída ✓" else "${percent.roundToInt()}%"
    progressGoal.progress = percent.roundToInt()
    txtAmounts.text = "${Money.format(goal.currentAmount)} de ${Money.format(goal.targetAmount)}"
    txtDeadline.text = goal.deadline?.let { "Prazo: ${Dates.format(it)}" }.orEmpty()
    txtDeadline.visibility = if (goal.deadline.isNullOrBlank()) android.view.View.GONE else android.view.View.VISIBLE
}

class GoalAdapter(
    private val onClick: (GoalDto) -> Unit,
) : ListAdapter<GoalDto, GoalAdapter.ViewHolder>(Diff) {

    class ViewHolder(val binding: ItemGoalBinding) : RecyclerView.ViewHolder(binding.root)

    override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): ViewHolder =
        ViewHolder(ItemGoalBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindViewHolder(holder: ViewHolder, position: Int) {
        val goal = getItem(position)
        holder.binding.bind(goal)
        holder.binding.root.setOnClickListener { onClick(goal) }
    }

    private object Diff : DiffUtil.ItemCallback<GoalDto>() {
        override fun areItemsTheSame(a: GoalDto, b: GoalDto) = a.id == b.id
        override fun areContentsTheSame(a: GoalDto, b: GoalDto) = a == b
    }
}
