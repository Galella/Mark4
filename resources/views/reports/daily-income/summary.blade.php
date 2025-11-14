@extends('layouts.adminlte')

@section('title', 'Daily Income Summary Report')

@section('content-header', 'Daily Income Summary Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reports.daily-income.index') }}">Daily Income Report</a></li>
    <li class="breadcrumb-item active">Summary</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daily Income Summary Report</h3>
                    <div class="card-tools">
                        <button id="copySummaryBtn" class="btn btn-primary btn-sm" onclick="copySummaryToClipboard()">
                            <i class="fas fa-copy"></i> Copy to Clipboard
                        </button>
                    </div>
                </div>
                <!-- Filter Form -->
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.daily-income.summary') }}">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="selected_date" class="form-label">Selected Date</label>
                                <input type="date" name="selected_date" id="selected_date" class="form-control" value="{{ request('selected_date', now()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="outlet_id" class="form-label">Outlet</label>
                                <select name="outlet_id" id="outlet_id" class="form-control">
                                    <option value="">All Outlets</option>
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                            {{ $outlet->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="moda_id" class="form-label">Moda</label>
                                <select name="moda_id" id="moda_id" class="form-control">
                                    <option value="">All Modas</option>
                                    @foreach($modas as $moda)
                                        <option value="{{ $moda->id }}" {{ request('moda_id') == $moda->id ? 'selected' : '' }}>
                                            {{ $moda->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6 d-flex">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="{{ route('reports.daily-income.summary') }}" class="btn btn-secondary">Reset</a>
                                <a href="{{ route('reports.daily-income.export-summary', request()->query()) }}" class="btn btn-success">Export to Excel</a>
                                <a href="{{ route('reports.daily-income.index') }}" class="btn btn-info">Detailed View</a>
                            </div>
                        </div>
                    </form>

                    <!-- Summary Content with Code Formatting -->
                    <div id="summary-content">
                        <div class="card card-outline card-primary">
                            <div class="card-body p-0">
                                <pre class="p-3 mb-0" style="background-color: #f8f9fa; white-space: pre-wrap; font-family: monospace; line-height: 1.5;">
<code>@if($user->isAdminOutlet())
Laporan Pendapatan {{ $user->outlet->name }}
@if($selectedDate)
Tanggal: {{ $selectedDate }}
@else
Tanggal: {{ now()->locale('id')->translatedFormat('l, d M Y') }}
@endif

@else
@if($selectedDate)
Laporan Pendapatan Kurir {{ optional($user->office)->name ?? 'Pusat' }} 
Tanggal: {{ $selectedDate }}
@else
Laporan Pendapatan Kurir {{ optional($user->office)->name ?? 'Pusat' }}
Tanggal: {{ now()->locale('id')->translatedFormat('l, d M Y') }}
@endif

@endif
@foreach($summaryByModa as $modaId => $data)
{{ $data['moda_name'] }}
* {{ number_format($data['total_colly'], 0, ',', '.') }} Koli
* {{ number_format($data['total_weight'], 0, ',', '.') }} Kg
* Rp {{ number_format($data['total_income'], 0, ',', '.') }}

@endforeach
Total
* {{ number_format($overallTotal['total_colly'], 0, ',', '.') }} Koli
* {{ number_format($overallTotal['total_weight'], 0, ',', '.') }} Kg
* Rp {{ number_format($overallTotal['total_income'], 0, ',', '.') }}

@if($user->isAdminOutlet())
* Demikian laporan dari {{ $user->outlet->name }}, Terimakasih.
@else
* Demikian laporan ini disampaikan, Terimakasih.
@endif
</code>
                                </pre>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden div for clipboard content -->
                    <div id="clipboard-content" class="d-none">
@if($user->isAdminOutlet())
Laporan Pendapatan {{ $user->outlet->name }}
@if($selectedDate)
Tanggal: {{ $selectedDate }}
@else
Tanggal: {{ now()->locale('id')->translatedFormat('l, d M Y') }}
@endif

@else
@if($selectedDate)
Laporan Pendapatan Kurir {{ optional($user->office)->name ?? 'Pusat' }} 
Tanggal: {{ $selectedDate }}
@else
Laporan Pendapatan Kurir {{ optional($user->office)->name ?? 'Pusat' }}
Tanggal: {{ now()->locale('id')->translatedFormat('l, d M Y') }}
@endif

@endif
@foreach($summaryByModa as $modaId => $data)
{{ $data['moda_name'] }}
* {{ number_format($data['total_colly'], 0, ',', '.') }} Koli
* {{ number_format($data['total_weight'], 0, ',', '.') }} Kg
* Rp {{ number_format($data['total_income'], 0, ',', '.') }}

@endforeach
Total
* {{ number_format($overallTotal['total_colly'], 0, ',', '.') }} Koli
* {{ number_format($overallTotal['total_weight'], 0, ',', '.') }} Kg
* Rp {{ number_format($overallTotal['total_income'], 0, ',', '.') }}

@if($user->isAdminOutlet())
* Demikian laporan dari {{ $user->outlet->name }}, Terimakasih.
@else
* Demikian laporan ini disampaikan, Terimakasih.
@endif
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
</div>

<script>
function copySummaryToClipboard() {
    const clipboardContent = document.getElementById('clipboard-content');
    const textToCopy = clipboardContent.textContent || clipboardContent.innerText;
    
    // Use modern clipboard API if available
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(textToCopy).then(function() {
            // Show success feedback
            const btn = document.getElementById('copySummaryBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        }).catch(function(err) {
            // Fallback to execCommand if clipboard API fails
            fallbackCopyTextToClipboard(textToCopy);
        });
    } else {
        // Fallback to execCommand
        fallbackCopyTextToClipboard(textToCopy);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    textArea.style.position = "fixed";
    textArea.style.left = "-999999px";
    textArea.style.top = "-999999px";
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        const btn = document.getElementById('copySummaryBtn');
        const originalText = btn.innerHTML;
        
        if (successful) {
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        } else {
            btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Copy Failed';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
    }
    
    document.body.removeChild(textArea);
}
</script>
@endsection