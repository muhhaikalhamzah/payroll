<x-app>
  <x-slot:title>Generate THR</x-slot:title>
<div class="pagetitle">
  <h1>Generate THR</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Home</a></li>
      <li class="breadcrumb-item"><a href="{{ route('thr-runs.index') }}">THR Runs</a></li>
      <li class="breadcrumb-item active">Generate</li>
    </ol>
  </nav>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Run Calculation Engine</h5>
          
          @if(session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          @endif

          <form method="POST" action="{{ route('thr-runs.store') }}">
            @csrf
            
            <div class="row mb-3">
              <label for="period_month" class="col-sm-3 col-form-label">Month</label>
              <div class="col-sm-9">
                <select name="period_month" id="period_month" class="form-select @error('period_month') is-invalid @enderror" required>
                    @for($i=1; $i<=12; $i++)
                        <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                    @endfor
                </select>
                @error('period_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>

            <div class="row mb-3">
              <label for="period_year" class="col-sm-3 col-form-label">Year</label>
              <div class="col-sm-9">
                <input type="number" name="period_year" id="period_year" class="form-control @error('period_year') is-invalid @enderror" value="{{ date('Y') }}" required>
                @error('period_year') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            
            <div class="text-center">
              <button type="submit" class="btn btn-primary">Generate Batch</button>
              <a href="{{ route('thr-runs.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>
</x-app>
