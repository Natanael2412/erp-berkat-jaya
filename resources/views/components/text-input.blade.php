@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 focus:border-brand-teal focus:ring-brand-teal rounded-md shadow-sm text-sm placeholder:text-slate-400']) }}>
