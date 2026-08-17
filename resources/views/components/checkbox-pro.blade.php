@props(['inputClass' => ''])
<div {{ $attributes->merge(['class' => 'checkbox-pro-container']) }}>
    <input type="checkbox" class="{{ $inputClass }}" {{ $attributes->except(['class', 'inputClass']) }}>
    <div class="checkmark"></div>
</div>
