import tkinter as tk
from tkinter import ttk, messagebox
import json
import os
from datetime import datetime

DATA_FILE = "expenses.json"

class ExpenseTracker:
    def __init__(self, root):
        self.root = root
        self.root.title("Expense Tracker")
        self.root.geometry("800x500")
        self.root.resizable(False, False)

        # Данные
        self.expenses = []
        self.load_data()

        # Поля ввода
        self.create_input_fields()
        # Таблица для отображения
        self.create_table()
        # Фильтры и подсчёт
        self.create_filters()
        # Обновить таблицу
        self.refresh_table()

    def create_input_fields(self):
        frame = tk.LabelFrame(self.root, text="Добавить расход", padx=10, pady=10)
        frame.pack(fill="x", padx=10, pady=5)

        tk.Label(frame, text="Сумма:").grid(row=0, column=0, sticky="e", padx=5, pady=5)
        self.amount_entry = tk.Entry(frame)
        self.amount_entry.grid(row=0, column=1, padx=5, pady=5)

        tk.Label(frame, text="Категория:").grid(row=0, column=2, sticky="e", padx=5, pady=5)
        self.category_var = tk.StringVar()
        self.category_combo = ttk.Combobox(frame, textvariable=self.category_var,
                                           values=["Еда", "Транспорт", "Развлечения", "Здоровье", "Другое"])
        self.category_combo.grid(row=0, column=3, padx=5, pady=5)
        self.category_combo.current(0)

        tk.Label(frame, text="Дата (ГГГГ-ММ-ДД):").grid(row=0, column=4, sticky="e", padx=5, pady=5)
        self.date_entry = tk.Entry(frame)
        self.date_entry.grid(row=0, column=5, padx=5, pady=5)
        self.date_entry.insert(0, datetime.now().strftime("%Y-%m-%d"))

        btn_add = tk.Button(frame, text="Добавить расход", command=self.add_expense, bg="lightgreen")
        btn_add.grid(row=0, column=6, padx=10, pady=5)

    def create_table(self):
        frame = tk.LabelFrame(self.root, text="Список расходов", padx=10, pady=10)
        frame.pack(fill="both", expand=True, padx=10, pady=5)

        columns = ("id", "amount", "category", "date")
        self.tree = ttk.Treeview(frame, columns=columns, show="headings")
        self.tree.heading("id", text="ID")
        self.tree.heading("amount", text="Сумма")
        self.tree.heading("category", text="Категория")
        self.tree.heading("date", text="Дата")
        self.tree.column("id", width=40)
        self.tree.column("amount", width=100)
        self.tree.column("category", width=120)
        self.tree.column("date", width=120)

        scrollbar = ttk.Scrollbar(frame, orient="vertical", command=self.tree.yview)
        self.tree.configure(yscrollcommand=scrollbar.set)
        self.tree.pack(side="left", fill="both", expand=True)
        scrollbar.pack(side="right", fill="y")

    def create_filters(self):
        frame = tk.LabelFrame(self.root, text="Фильтрация и подсчёт", padx=10, pady=10)
        frame.pack(fill="x", padx=10, pady=5)

        tk.Label(frame, text="Фильтр по категории:").grid(row=0, column=0, sticky="e", padx=5, pady=5)
        self.filter_category = tk.StringVar(value="Все")
        cat_filter_combo = ttk.Combobox(frame, textvariable=self.filter_category,
                                        values=["Все", "Еда", "Транспорт", "Развлечения", "Здоровье", "Другое"])
        cat_filter_combo.grid(row=0, column=1, padx=5, pady=5)

        tk.Label(frame, text="Период с (ГГГГ-ММ-ДД):").grid(row=0, column=2, sticky="e", padx=5, pady=5)
        self.start_date = tk.Entry(frame)
        self.start_date.grid(row=0, column=3, padx=5, pady=5)

        tk.Label(frame, text="по (ГГГГ-ММ-ДД):").grid(row=0, column=4, sticky="e", padx=5, pady=5)
        self.end_date = tk.Entry(frame)
        self.end_date.grid(row=0, column=5, padx=5, pady=5)

        btn_filter = tk.Button(frame, text="Применить фильтр", command=self.refresh_table)
        btn_filter.grid(row=0, column=6, padx=5, pady=5)

        btn_sum = tk.Button(frame, text="Подсчитать сумму за период", command=self.show_sum)
        btn_sum.grid(row=0, column=7, padx=5, pady=5)

        self.sum_label = tk.Label(frame, text="Сумма за период: 0.00", fg="blue")
        self.sum_label.grid(row=1, column=0, columnspan=8, pady=5)

    def add_expense(self):
        amount_str = self.amount_entry.get().strip()
        category = self.category_var.get().strip()
        date_str = self.date_entry.get().strip()

        # Валидация суммы
        try:
            amount = float(amount_str)
            if amount <= 0:
                raise ValueError("Сумма должна быть положительной")
        except ValueError:
            messagebox.showerror("Ошибка", "Сумма должна быть положительным числом")
            return

        # Валидация категории
        if not category:
            messagebox.showerror("Ошибка", "Выберите категорию")
            return

        # Валидация даты
        try:
            datetime.strptime(date_str, "%Y-%m-%d")
        except ValueError:
            messagebox.showerror("Ошибка", "Дата должна быть в формате ГГГГ-ММ-ДД")
            return

        # Добавление записи
        new_id = max([e["id"] for e in self.expenses], default=0) + 1
        self.expenses.append({
            "id": new_id,
            "amount": amount,
            "category": category,
            "date": date_str
        })
        self.save_data()
        self.clear_inputs()
        self.refresh_table()
        messagebox.showinfo("Успех", "Расход добавлен")

    def clear_inputs(self):
        self.amount_entry.delete(0, tk.END)
        self.date_entry.delete(0, tk.END)
        self.date_entry.insert(0, datetime.now().strftime("%Y-%m-%d"))
        self.category_combo.current(0)

    def refresh_table(self):
        # Очистить таблицу
        for row in self.tree.get_children():
            self.tree.delete(row)

        # Получить фильтр
        cat_filter = self.filter_category.get()
        start = self.start_date.get().strip()
        end = self.end_date.get().strip()

        filtered = self.expenses[:]
        if cat_filter != "Все":
            filtered = [e for e in filtered if e["category"] == cat_filter]
        if start:
            try:
                start_dt = datetime.strptime(start, "%Y-%m-%d")
                filtered = [e for e in filtered if datetime.strptime(e["date"], "%Y-%m-%d") >= start_dt]
            except ValueError:
                pass  # игнорируем неверный формат
        if end:
            try:
                end_dt = datetime.strptime(end, "%Y-%m-%d")
                filtered = [e for e in filtered if datetime.strptime(e["date"], "%Y-%m-%d") <= end_dt]
            except ValueError:
                pass

        # Заполнить таблицу
        for e in filtered:
            self.tree.insert("", tk.END, values=(e["id"], e["amount"], e["category"], e["date"]))

        # Подсчёт суммы для отображения в label (можно вызвать автоматически)
        self.update_sum_label(start, end, cat_filter)

    def update_sum_label(self, start, end, cat_filter):
        filtered = self.expenses[:]
        if cat_filter != "Все":
            filtered = [e for e in filtered if e["category"] == cat_filter]
        if start:
            try:
                start_dt = datetime.strptime(start, "%Y-%m-%d")
                filtered = [e for e in filtered if datetime.strptime(e["date"], "%Y-%m-%d") >= start_dt]
            except:
                pass
        if end:
            try:
                end_dt = datetime.strptime(end, "%Y-%m-%d")
                filtered = [e for e in filtered if datetime.strptime(e["date"], "%Y-%m-%d") <= end_dt]
            except:
                pass
        total = sum(e["amount"] for e in filtered)
        self.sum_label.config(text=f"Сумма (с учётом фильтра): {total:.2f}")

    def show_sum(self):
        start = self.start_date.get().strip()
        end = self.end_date.get().strip()
        cat_filter = self.filter_category.get()

        filtered = self.expenses[:]
        if cat_filter != "Все":
            filtered = [e for e in filtered if e["category"] == cat_filter]
        if start:
            try:
                start_dt = datetime.strptime(start, "%Y-%m-%d")
                filtered = [e for e in filtered if datetime.strptime(e["date"], "%Y-%m-%d") >= start_dt]
            except ValueError:
                messagebox.showerror("Ошибка", "Неверный формат начальной даты")
                return
        if end:
            try:
                end_dt = datetime.strptime(end, "%Y-%m-%d")
                filtered = [e for e in filtered if datetime.strptime(e["date"], "%Y-%m-%d") <= end_dt]
            except ValueError:
                messagebox.showerror("Ошибка", "Неверный формат конечной даты")
                return
        total = sum(e["amount"] for e in filtered)
        messagebox.showinfo("Сумма расходов", f"Общая сумма за выбранный период: {total:.2f}")

    def load_data(self):
        if os.path.exists(DATA_FILE):
            with open(DATA_FILE, "r", encoding="utf-8") as f:
                self.expenses = json.load(f)
        else:
            self.expenses = []

    def save_data(self):
        with open(DATA_FILE, "w", encoding="utf-8") as f:
            json.dump(self.expenses, f, ensure_ascii=False, indent=4)

if __name__ == "__main__":
    root = tk.Tk()
    app = ExpenseTracker(root)
    root.mainloop()