package com.finaxis.finaxis_mobile.util

import android.content.DialogInterface
import android.content.Context
import android.view.View
import android.widget.ArrayAdapter
import android.widget.AutoCompleteTextView
import androidx.appcompat.app.AlertDialog
import androidx.fragment.app.Fragment
import com.finaxis.finaxis_mobile.FinaxisApp
import com.finaxis.finaxis_mobile.data.repository.FinanceRepository
import com.google.android.material.dialog.MaterialAlertDialogBuilder

/** Atalho para o repositório nas telas: val repo = financeRepository() */
fun Fragment.financeRepository(): FinanceRepository =
    (requireActivity().application as FinaxisApp).financeRepository

/**
 * Abre um diálogo com formulário.
 * Diferente do padrão do Android, o botão "Salvar" NÃO fecha sozinho:
 * quem fecha é o onSave (chamando dialog.dismiss()) depois que a API aceitar.
 * Assim, se der erro de validação, o usuário não perde o que digitou.
 */
fun showFormDialog(
    context: Context,
    title: String,
    view: View,
    positiveText: String = "Salvar",
    neutralText: String? = null,
    onNeutral: ((AlertDialog) -> Unit)? = null,
    onSave: (AlertDialog) -> Unit,
): AlertDialog {
    val builder = MaterialAlertDialogBuilder(context)
        .setTitle(title)
        .setView(view)
        .setPositiveButton(positiveText, null)
        .setNegativeButton("Cancelar", null)
    if (neutralText != null) builder.setNeutralButton(neutralText, null)

    val dialog = builder.create()
    dialog.setOnShowListener {
        dialog.getButton(DialogInterface.BUTTON_POSITIVE).setOnClickListener { onSave(dialog) }
        if (neutralText != null && onNeutral != null) {
            dialog.getButton(DialogInterface.BUTTON_NEUTRAL).setOnClickListener { onNeutral(dialog) }
        }
    }
    dialog.show()
    return dialog
}

/** Pergunta "tem certeza?" antes de excluir. */
fun confirm(context: Context, title: String, message: String, confirmText: String = "Excluir", onConfirm: () -> Unit) {
    MaterialAlertDialogBuilder(context)
        .setTitle(title)
        .setMessage(message)
        .setPositiveButton(confirmText) { _, _ -> onConfirm() }
        .setNegativeButton("Cancelar", null)
        .show()
}

/**
 * Preenche um campo de lista suspensa (AutoCompleteTextView do Material).
 * onSelected recebe a posição escolhida.
 */
fun AutoCompleteTextView.setOptions(options: List<String>, selected: Int = -1, onSelected: (Int) -> Unit) {
    setAdapter(ArrayAdapter(context, android.R.layout.simple_list_item_1, options))
    if (selected in options.indices) {
        setText(options[selected], false)
        onSelected(selected)
    } else {
        setText("", false)
    }
    setOnItemClickListener { _, _, position, _ -> onSelected(position) }
}
