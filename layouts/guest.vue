<template>
    <div class="guest-body">
        <div>
            <div class="guest-card">
                <div class="guest-brand">
                    <div class="guest-brand-mark">جس</div>
                    <div class="guest-brand-text">جلالی سهام</div>
                </div>

                <slot />
            </div>

            <p class="guest-footer-note">پایش و آلرت بازار بورس ایران</p>
        </div>
    </div>
</template>

<style scoped>
.guest-body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: radial-gradient(circle at 30% 20%, #16233c 0%, #0c1526 65%);
    padding: 1.5rem;
}

.guest-card {
    width: 100%;
    max-width: 400px;
    background: var(--surface-card);
    border-radius: var(--radius-md);
    padding: 2.25rem 2rem;
    box-shadow: 0 20px 45px rgba(6, 12, 26, 0.35);
}

.guest-brand {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    margin-bottom: 1.75rem;
}

.guest-brand-mark {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-sm);
    background: var(--gold-tint);
    border: 1px solid var(--gold-500);
    color: var(--gold-600);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}

.guest-brand-text {
    font-weight: 700;
    font-size: 1.05rem;
}

.guest-footer-note {
    text-align: center;
    margin-top: 1.5rem;
    font-size: 0.78rem;
    color: var(--text-on-navy-dim);
}
</style>
