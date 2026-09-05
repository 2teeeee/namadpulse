@php
    $symbol = $symbol ?? null;
@endphp

<div class="row g-3">
    <div class="col-12 col-md-6">
        <label for="ticker" class="form-label">نماد (ticker)</label>
        <input
            type="text"
            name="ticker"
            id="ticker"
            class="form-control @error('ticker') is-invalid @enderror"
            placeholder="مثلاً فولاد"
            value="{{ old('ticker', $symbol?->ticker) }}"
            required
        >
        @error('ticker')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="symbol_group_id" class="form-label">گروه نماد</label>
        <select name="symbol_group_id" id="symbol_group_id" class="form-select @error('symbol_group_id') is-invalid @enderror" required>
            <option value="">— انتخاب کنید —</option>
            @foreach ($symbolGroups as $group)
                <option value="{{ $group->id }}" @selected(old('symbol_group_id', $symbol?->symbol_group_id) == $group->id)>
                    {{ $group->name }}
                </option>
            @endforeach
        </select>
        @error('symbol_group_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="name" class="form-label">نام کامل شرکت (فارسی)</label>
        <input
            type="text"
            name="name"
            id="name"
            class="form-control @error('name') is-invalid @enderror"
            placeholder="مثلاً فولاد مبارکه اصفهان"
            value="{{ old('name', $symbol?->name) }}"
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="name_en" class="form-label">نام لاتین (اختیاری)</label>
        <input
            type="text"
            name="name_en"
            id="name_en"
            class="form-control @error('name_en') is-invalid @enderror"
            value="{{ old('name_en', $symbol?->name_en) }}"
        >
        @error('name_en')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="isin_code" class="form-label">کد ISIN (اختیاری)</label>
        <input
            type="text"
            name="isin_code"
            id="isin_code"
            class="form-control tabular-nums @error('isin_code') is-invalid @enderror"
            placeholder="۱۲ کاراکتری"
            value="{{ old('isin_code', $symbol?->isin_code) }}"
        >
        @error('isin_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="tsetmc_id" class="form-label">شناسه TSETMC (اختیاری)</label>
        <input
            type="text"
            name="tsetmc_id"
            id="tsetmc_id"
            class="form-control tabular-nums @error('tsetmc_id') is-invalid @enderror"
            placeholder="برای اتصال به brsapi لازم است"
            value="{{ old('tsetmc_id', $symbol?->tsetmc_id) }}"
        >
        @error('tsetmc_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="board" class="form-label">بازار</label>
        <select name="board" id="board" class="form-select @error('board') is-invalid @enderror" required>
            @foreach (['bourse' => 'بازار اول/دوم بورس', 'farabourse' => 'فرابورس', 'other' => 'سایر'] as $value => $label)
                <option value="{{ $value }}" @selected(old('board', $symbol?->board ?? 'bourse') == $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('board')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-6 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <input type="hidden" name="is_active" value="0">
            <input
                type="checkbox"
                name="is_active"
                id="is_active"
                class="form-check-input"
                value="1"
                @checked(old('is_active', $symbol?->is_active ?? true))
            >
            <label for="is_active" class="form-check-label">نماد فعال است</label>
        </div>
    </div>
</div>
