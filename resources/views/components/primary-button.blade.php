<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-brand-teal border border-transparent rounded-md font-bold text-sm text-white tracking-wide shadow-sm hover:bg-brand-teal-deep focus:bg-brand-teal-deep active:translate-y-px focus:outline-none focus:ring-2 focus:ring-brand-teal focus:ring-offset-2 transition duration-150']) }}>
    {{ $slot }}
</button>
