<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DevOS — Forgot Password</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #13131a inset !important;
            -webkit-text-fill-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .fp-otp-input {
            letter-spacing: 0.35em;
            text-align: center;
            font-weight: 700;
        }
    </style>
</head>

<body class="font-sans antialiased bg-[#0f0f13] min-h-screen flex items-center justify-center p-6 relative">

    <div
        class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:40px_40px]">
    </div>

    <div
        class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-[var(--color-dp-primary)] rounded-full blur-[120px] opacity-20 pointer-events-none">
    </div>

    <div
        class="w-full max-w-[440px] p-6 sm:p-10 relative overflow-hidden rounded-3xl bg-white/5 backdrop-blur-xl border border-white/10 shadow-[0_8px_32px_0_rgba(0,0,0,0.36)] text-white">

        <div class="relative z-10 text-center mb-8">
            <div class="relative w-16 h-16 mx-auto mb-6 group">
                <div
                    class="absolute inset-0 bg-blue-500 rounded-xl blur-xl opacity-30 group-hover:opacity-60 transition-opacity duration-700 animate-pulse">
                </div>
                <div
                    class="relative w-full h-full flex items-center justify-center rounded-xl overflow-hidden bg-[#0f0f13] border border-blue-500/30 shadow-[0_0_20px_rgba(59,130,246,0.4)]">
                    <img src="{{ asset('devos_logo.png') }}" alt="Logo"
                        class="w-full h-full object-cover scale-[1.35]" />
                </div>
            </div>
            <h2 id="fpTitle" class="text-3xl font-bold text-white mb-3 tracking-tight">Forgot Password</h2>
            <p id="fpSubtitle" class="text-[14px] text-gray-300 leading-relaxed max-w-[320px] mx-auto">
                No worries, we'll send you reset instructions.
            </p>
        </div>

        {{-- Step 1: Email --}}
        <form id="fpEmailForm" class="relative z-10 flex flex-col gap-5">
            <div>
                <label for="email" class="block text-[13px] font-semibold text-gray-300 mb-2">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email', $defaultEmail) }}"
                    class="w-full bg-[#13131a] border border-white/10 rounded-xl px-4 py-3.5 text-[14px] text-white outline-none focus:border-blue-500/50 focus:bg-[#1a1a24] focus:ring-1 focus:ring-blue-500/50 transition-all placeholder-gray-600"
                    placeholder="Enter your email" required autocomplete="email">
            </div>
            <button type="submit" id="fpSendBtn"
                class="relative group w-full h-[52px] flex items-center justify-center bg-[var(--color-dp-primary)] hover:bg-[#4b33a8] text-white font-semibold rounded-xl transition-all duration-300 overflow-hidden shadow-[0_4px_14px_0_rgba(92,65,201,0.39)] hover:shadow-[0_6px_20px_rgba(92,65,201,0.23)] hover:-translate-y-0.5">
                <span id="fpSendLabel" class="transition-opacity duration-300">Reset Password</span>
                <span id="fpSendLoader" class="absolute inset-0 hidden items-center justify-center">
                    <x-hourglass-loader size="sm" />
                </span>
            </button>
        </form>

        {{-- Step 2: OTP --}}
        <form id="fpOtpForm" class="relative z-10 hidden flex-col gap-5">
            <div>
                <label for="otp" class="block text-[13px] font-semibold text-gray-300 mb-2">Verification Code</label>
                <input type="text" id="otp" name="otp" maxlength="6" inputmode="numeric" pattern="[0-9]*"
                    class="fp-otp-input w-full bg-[#13131a] border border-white/10 rounded-xl px-4 py-3.5 text-[18px] text-white outline-none focus:border-blue-500/50 focus:bg-[#1a1a24] focus:ring-1 focus:ring-blue-500/50 transition-all placeholder-gray-600"
                    placeholder="000000" required autocomplete="one-time-code">
                <p class="text-[12px] text-gray-500 mt-2">Enter the 6-digit code sent to your email.</p>
            </div>
            <button type="submit" id="fpVerifyBtn"
                class="relative w-full h-[52px] flex items-center justify-center bg-[var(--color-dp-primary)] hover:bg-[#4b33a8] text-white font-semibold rounded-xl transition-all duration-300 shadow-[0_4px_14px_0_rgba(92,65,201,0.39)]">
                <span id="fpVerifyLabel">Verify Code</span>
                <span id="fpVerifyLoader" class="absolute inset-0 hidden items-center justify-center">
                    <x-hourglass-loader size="sm" />
                </span>
            </button>
            <button type="button" id="fpResendBtn"
                class="text-[13px] font-semibold text-[var(--color-dp-primary)] hover:text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Resend code
            </button>
        </form>

        {{-- Step 3: New password --}}
        <form id="fpResetForm" class="relative z-10 hidden flex-col gap-5">
            <div>
                <label for="password" class="block text-[13px] font-semibold text-gray-300 mb-2">New Password</label>
                <input type="password" id="password" name="password"
                    class="w-full bg-[#13131a] border border-white/10 rounded-xl px-4 py-3.5 text-[14px] text-white outline-none focus:border-blue-500/50 focus:bg-[#1a1a24] focus:ring-1 focus:ring-blue-500/50 transition-all"
                    placeholder="Enter new password" required autocomplete="new-password">
            </div>
            <div>
                <label for="password_confirmation"
                    class="block text-[13px] font-semibold text-gray-300 mb-2">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="w-full bg-[#13131a] border border-white/10 rounded-xl px-4 py-3.5 text-[14px] text-white outline-none focus:border-blue-500/50 focus:bg-[#1a1a24] focus:ring-1 focus:ring-blue-500/50 transition-all"
                    placeholder="Confirm new password" required autocomplete="new-password">
            </div>
            <button type="submit" id="fpResetBtn"
                class="relative w-full h-[52px] flex items-center justify-center bg-[var(--color-dp-primary)] hover:bg-[#4b33a8] text-white font-semibold rounded-xl transition-all duration-300 shadow-[0_4px_14px_0_rgba(92,65,201,0.39)]">
                <span id="fpResetLabel">Save New Password</span>
                <span id="fpResetLoader" class="absolute inset-0 hidden items-center justify-center">
                    <x-hourglass-loader size="sm" />
                </span>
            </button>
        </form>

        <div class="mt-8 text-center relative z-10">
            <a href="{{ route('login') }}"
                class="text-[14px] font-semibold text-[var(--color-dp-primary)] hover:text-white transition-colors inline-flex items-center gap-1.5">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Back to log in
            </a>
        </div>
    </div>

    {{-- Choice modal --}}
    <div id="fpChoiceModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div
            class="relative z-10 w-full max-w-md rounded-2xl bg-[#1a1a24] border border-white/10 p-8 shadow-2xl text-white">
            <h3 class="text-[20px] font-bold mb-2 text-center">Verification successful</h3>
            <p class="text-[14px] text-gray-400 text-center mb-6">What would you like to do next?</p>
            <div class="flex flex-col gap-3">
                <button type="button" id="fpChooseReset"
                    class="w-full h-[48px] rounded-xl bg-[var(--color-dp-primary)] hover:bg-[#4b33a8] text-white font-semibold transition-all">
                    Reset Password
                </button>
                <button type="button" id="fpChooseDashboard"
                    class="w-full h-[48px] rounded-xl border border-white/15 text-white font-semibold hover:bg-white/5 transition-all">
                    Go to Dashboard
                </button>
            </div>
        </div>
    </div>

    @include('components.alert-stack')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.DevOSAlert) window.DevOSAlert.init();

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const emailInput = document.getElementById('email');
            const otpInput = document.getElementById('otp');
            const emailForm = document.getElementById('fpEmailForm');
            const otpForm = document.getElementById('fpOtpForm');
            const resetForm = document.getElementById('fpResetForm');
            const choiceModal = document.getElementById('fpChoiceModal');
            const fpTitle = document.getElementById('fpTitle');
            const fpSubtitle = document.getElementById('fpSubtitle');

            const jsonHeaders = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            };

            const alertOk = (title, desc) => window.DevOSAlert?.success(title, desc);
            const alertErr = (title, desc) => window.DevOSAlert?.error(title, desc);

            const parseJson = async (res) => ({
                status: res.status,
                body: await res.json().catch(() => ({})),
            });

            const setLoading = (btn, labelEl, loaderEl, on, labelText) => {
                if (!btn) return;
                btn.disabled = on;
                btn.classList.toggle('pointer-events-none', on);
                labelEl?.classList.toggle('opacity-0', on);
                if (loaderEl) {
                    loaderEl.classList.toggle('hidden', !on);
                    loaderEl.classList.toggle('flex', on);
                }
                if (labelText && labelEl) labelEl.textContent = labelText;
            };

            const showStep = (step) => {
                emailForm.classList.toggle('hidden', step !== 'email');
                emailForm.classList.toggle('flex', step === 'email');
                otpForm.classList.toggle('hidden', step !== 'otp');
                otpForm.classList.toggle('flex', step === 'otp');
                resetForm.classList.toggle('hidden', step !== 'reset');
                resetForm.classList.toggle('flex', step === 'reset');

                if (step === 'email') {
                    fpTitle.textContent = 'Forgot Password';
                    fpSubtitle.textContent = "No worries, we'll send you reset instructions.";
                } else if (step === 'otp') {
                    fpTitle.textContent = 'Enter Code';
                    fpSubtitle.textContent = 'We sent a verification code to your email.';
                } else if (step === 'reset') {
                    fpTitle.textContent = 'New Password';
                    fpSubtitle.textContent = 'Choose a new password for your account.';
                }
            };

            const openChoiceModal = () => {
                choiceModal.classList.remove('hidden');
                choiceModal.classList.add('flex');
            };

            const closeChoiceModal = () => {
                choiceModal.classList.add('hidden');
                choiceModal.classList.remove('flex');
            };

            emailForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                const sendBtn = document.getElementById('fpSendBtn');
                const sendLabel = document.getElementById('fpSendLabel');
                const sendLoader = document.getElementById('fpSendLoader');
                setLoading(sendBtn, sendLabel, sendLoader, true, 'Sending...');

                try {
                    const res = await parseJson(await fetch('{{ route('password.forgot.send') }}', {
                        method: 'POST',
                        headers: jsonHeaders,
                        body: JSON.stringify({ email: emailInput.value.trim() }),
                    }));

                    if (res.body.success) {
                        alertOk('Code sent', res.body.message || 'Verification code sent.');
                        otpInput.value = '';
                        showStep('otp');
                        otpInput.focus();
                    } else {
                        alertErr('Unable to send code', res.body.message || 'Please try again.');
                    }
                } catch {
                    alertErr('Unable to send code', 'Network error. Please try again.');
                } finally {
                    setLoading(sendBtn, sendLabel, sendLoader, false, 'Reset Password');
                }
            });

            document.getElementById('fpResendBtn')?.addEventListener('click', () => {
                emailForm.requestSubmit();
            });

            otpForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                const verifyBtn = document.getElementById('fpVerifyBtn');
                const verifyLabel = document.getElementById('fpVerifyLabel');
                const verifyLoader = document.getElementById('fpVerifyLoader');
                setLoading(verifyBtn, verifyLabel, verifyLoader, true, 'Verifying...');

                try {
                    const res = await parseJson(await fetch('{{ route('password.forgot.verify') }}', {
                        method: 'POST',
                        headers: jsonHeaders,
                        body: JSON.stringify({
                            email: emailInput.value.trim(),
                            otp: otpInput.value.trim(),
                        }),
                    }));

                    if (res.body.success) {
                        alertOk('Verified', res.body.message || 'Verification successful.');
                        openChoiceModal();
                    } else {
                        alertErr('Verification failed', res.body.message || 'Incorrect code.');
                        otpInput.focus();
                    }
                } catch {
                    alertErr('Verification failed', 'Network error. Please try again.');
                } finally {
                    setLoading(verifyBtn, verifyLabel, verifyLoader, false, 'Verify Code');
                }
            });

            document.getElementById('fpChooseReset')?.addEventListener('click', () => {
                closeChoiceModal();
                showStep('reset');
                document.getElementById('password')?.focus();
            });

            document.getElementById('fpChooseDashboard')?.addEventListener('click', async () => {
                closeChoiceModal();
                try {
                    const res = await parseJson(await fetch('{{ route('password.forgot.dashboard') }}', {
                        method: 'POST',
                        headers: jsonHeaders,
                        body: JSON.stringify({ email: emailInput.value.trim() }),
                    }));

                    if (res.body.success && res.body.redirect) {
                        window.location.href = res.body.redirect;
                        return;
                    }
                    alertErr('Sign in failed', res.body.message || 'Unable to continue.');
                } catch {
                    alertErr('Sign in failed', 'Network error. Please try again.');
                }
            });

            resetForm?.addEventListener('submit', async (event) => {
                event.preventDefault();
                const resetBtn = document.getElementById('fpResetBtn');
                const resetLabel = document.getElementById('fpResetLabel');
                const resetLoader = document.getElementById('fpResetLoader');
                setLoading(resetBtn, resetLabel, resetLoader, true, 'Saving...');

                try {
                    const res = await parseJson(await fetch('{{ route('password.forgot.reset') }}', {
                        method: 'POST',
                        headers: jsonHeaders,
                        body: JSON.stringify({
                            email: emailInput.value.trim(),
                            password: document.getElementById('password').value,
                            password_confirmation: document.getElementById('password_confirmation').value,
                        }),
                    }));

                    if (res.body.success && res.body.redirect) {
                        alertOk('done successfully :)', res.body.message || 'Password updated.');
                        setTimeout(() => {
                            window.location.href = res.body.redirect;
                        }, 700);
                        return;
                    }
                    alertErr('Update failed', res.body.message || 'Unable to update password.');
                } catch {
                    alertErr('Update failed', 'Network error. Please try again.');
                } finally {
                    setLoading(resetBtn, resetLabel, resetLoader, false, 'Save New Password');
                }
            });

            otpInput?.addEventListener('input', () => {
                otpInput.value = otpInput.value.replace(/\D/g, '').slice(0, 6);
            });
        });
    </script>
</body>

</html>
