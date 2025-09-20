# report.py - Pulls expenses from MySQL and generates simple Pandas reports
import pandas as pd
import mysql.connector
import matplotlib.pyplot as plt
import os

DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'expense_manager'
}

def get_df():
    conn = mysql.connector.connect(**DB_CONFIG)
    query = """SELECT e.date, c.name as category, e.amount
               FROM expenses e
               LEFT JOIN categories c ON e.category_id=c.id"""
    df = pd.read_sql(query, conn)
    conn.close()
    return df

def category_report(df):
    if df.empty:
        print('No data available.')
        return
    summary = df.groupby('category')['amount'].sum().sort_values(ascending=False)
    print('\nCategory-wise totals:')
    print(summary)
    ax = summary.plot(kind='bar', title='Expenses by Category', ylabel='Amount (₹)')
    fig = ax.get_figure()
    fig.tight_layout()
    out = os.path.join(os.getcwd(), 'category_report.png')
    fig.savefig(out)
    print(f'Category report saved to {out}')

def monthly_report(df):
    if df.empty:
        return
    df['date'] = pd.to_datetime(df['date'])
    monthly = df.groupby(df['date'].dt.to_period('M'))['amount'].sum()
    print('\nMonthly totals:')
    print(monthly)
    ax = monthly.plot(kind='line', marker='o', title='Monthly Expenses', ylabel='Amount (₹)')
    fig = ax.get_figure()
    fig.tight_layout()
    out = os.path.join(os.getcwd(), 'monthly_report.png')
    fig.savefig(out)
    print(f'Monthly report saved to {out}')

def main():
    df = get_df()
    category_report(df)
    monthly_report(df)

if __name__ == '__main__':
    main()
