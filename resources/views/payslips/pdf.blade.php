<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $payslip->employee->user->first_name }} {{ $payslip->employee->user->last_name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; }
        .period { font-size: 14px; color: #555; }
        .info-table, .money-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .money-table th, .money-table td { border: 1px solid #ccc; padding: 8px; }
        .money-table th { background-color: #f4f4f4; text-align: left; }
        .amount { text-align: right; }
        .total-row { font-weight: bold; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .net-pay { font-size: 16px; font-weight: bold; text-align: right; background-color: #e9ecef; padding: 10px; margin-top: 20px;}
    </style>
</head>
<body>

    <div class="header">
        <div class="title">PAYSLIP</div>
        <div class="period">{{ date("F", mktime(0, 0, 0, $payslip->payrollRun->period_month, 1)) }} {{ $payslip->payrollRun->period_year }}</div>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%"><strong>Employee Name:</strong></td>
            <td width="30%">{{ $payslip->employee->user->first_name }} {{ $payslip->employee->user->last_name }}</td>
            <td width="20%"><strong>Department:</strong></td>
            <td width="30%">{{ $payslip->employee->department?->name }}</td>
        </tr>
        <tr>
            <td><strong>NIK:</strong></td>
            <td>{{ $payslip->employee->nik }}</td>
            <td><strong>Position:</strong></td>
            <td>{{ $payslip->employee->position?->name }}</td>
        </tr>
    </table>

    <table class="money-table">
        <tr>
            <th width="50%">Earnings</th>
            <th width="50%">Deductions</th>
        </tr>
        <tr>
            <td valign="top">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>Basic Salary</td>
                        <td class="amount">Rp {{ number_format($payslip->basic_salary, 0, ',', '.') }}</td>
                    </tr>
                    @foreach($payslip->components->where('type', 'allowance') as $allowance)
                    <tr>
                        <td>{{ $allowance->name }}</td>
                        <td class="amount">Rp {{ number_format($allowance->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr><td colspan="2"><hr></td></tr>
                    <tr class="total-row">
                        <td>Total Earnings</td>
                        <td class="amount">Rp {{ number_format($payslip->basic_salary + $payslip->total_allowances, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
            <td valign="top">
                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    @foreach($payslip->components->where('type', 'deduction') as $deduction)
                    <tr>
                        <td>{{ $deduction->name }}</td>
                        <td class="amount text-danger">- Rp {{ number_format($deduction->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr><td colspan="2"><hr></td></tr>
                    <tr class="total-row">
                        <td>Total Deductions</td>
                        <td class="amount text-danger">- Rp {{ number_format($payslip->total_deductions, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="net-pay">
        NET PAY: <span class="text-success">Rp {{ number_format($payslip->net_pay, 0, ',', '.') }}</span>
    </div>

</body>
</html>
