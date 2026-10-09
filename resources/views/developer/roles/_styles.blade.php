<style>
    .dv-wrap { max-width: 1240px; margin: 0 auto; padding: 1.75rem 1rem 3rem; font-family: 'Cairo', sans-serif; color: #0f172a; }

    /* ---------- Hero ---------- */
    .dv-hero {
        position: relative; overflow: hidden; border-radius: 22px; padding: 1.75rem 1.75rem;
        background: radial-gradient(1200px 300px at 100% 0%, rgba(167,139,250,.35), transparent 60%),
                    radial-gradient(800px 260px at 0% 100%, rgba(56,189,248,.25), transparent 60%),
                    linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #0f172a 100%);
        color: #fff; box-shadow: 0 20px 45px -20px rgba(49,46,129,.6); margin-bottom: 1.5rem;
    }
    .dv-hero::after {
        content: ''; position: absolute; inset: 0; pointer-events: none;
        background-image: linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
        background-size: 28px 28px; mask-image: linear-gradient(to bottom, #000, transparent);
    }
    .dv-hero-row { position: relative; z-index: 1; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; }
    .dv-badge-dev {
        display: inline-flex; align-items: center; gap: .4rem; font-size: .72rem; font-weight: 700; letter-spacing: .5px;
        background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.2); padding: .25rem .7rem; border-radius: 999px; backdrop-filter: blur(6px);
    }
    .dv-hero h1 { font-size: 1.65rem; font-weight: 800; margin: .55rem 0 .25rem; color: #fff; }
    .dv-hero p { margin: 0; color: #c7d2fe; font-size: .92rem; }
    .dv-crumbs { position: relative; z-index: 1; font-size: .8rem; color: #a5b4fc; margin-bottom: .35rem; }
    .dv-crumbs a { color: #e0e7ff; text-decoration: none; }
    .dv-crumbs a:hover { text-decoration: underline; }

    /* ---------- Buttons ---------- */
    .dv-btn {
        display: inline-flex; align-items: center; gap: .45rem; border: 0; cursor: pointer; text-decoration: none;
        font-weight: 700; font-size: .88rem; padding: .62rem 1.1rem; border-radius: 12px; transition: all .2s ease; font-family: inherit; line-height: 1.2;
    }
    .dv-btn:hover { transform: translateY(-1px); }
    .dv-btn-primary { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: #fff; box-shadow: 0 8px 20px -8px rgba(99,102,241,.7); }
    .dv-btn-primary:hover { color: #fff; box-shadow: 0 12px 26px -8px rgba(99,102,241,.85); }
    .dv-btn-light { background: rgba(255,255,255,.12); color: #fff; border: 1px solid rgba(255,255,255,.25); }
    .dv-btn-light:hover { background: rgba(255,255,255,.2); color: #fff; }
    .dv-btn-ghost { background: #fff; color: #334155; border: 1px solid #e2e8f0; }
    .dv-btn-ghost:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }
    .dv-btn-danger { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
    .dv-btn-danger:hover { background: #ffe4e6; color: #9f1239; }
    .dv-btn-sm { padding: .38rem .7rem; font-size: .78rem; border-radius: 9px; }

    /* ---------- Stats ---------- */
    .dv-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: .9rem; margin-bottom: 1.5rem; }
    .dv-stat { background: #fff; border: 1px solid #eef2f7; border-radius: 16px; padding: 1rem 1.1rem; display: flex; align-items: center; gap: .85rem; box-shadow: 0 1px 2px rgba(15,23,42,.04); }
    .dv-stat-icon { width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; font-size: 1.25rem; }
    .dv-stat b { display: block; font-size: 1.35rem; font-weight: 800; line-height: 1.1; }
    .dv-stat span { font-size: .78rem; color: #64748b; font-weight: 600; }

    /* ---------- Section ---------- */
    .dv-section-title { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin: 0 0 .9rem; flex-wrap: wrap; }
    .dv-section-title h2 { font-size: 1.1rem; font-weight: 800; margin: 0; color: #0f172a; }
    .dv-section-title small { color: #64748b; font-size: .8rem; font-weight: 600; }

    .dv-card { background: #fff; border: 1px solid #eef2f7; border-radius: 18px; box-shadow: 0 1px 3px rgba(15,23,42,.05); }
    .dv-card-pad { padding: 1.25rem; }

    /* ---------- Role cards ---------- */
    .dv-roles { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
    .dv-role {
        --c: #64748b; position: relative; background: #fff; border: 1px solid #eef2f7; border-radius: 18px; padding: 1.15rem 1.15rem 1rem;
        transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease; overflow: hidden; display: flex; flex-direction: column;
    }
    .dv-role::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 4px; background: var(--c); }
    .dv-role::after { content: ''; position: absolute; width: 140px; height: 140px; border-radius: 50%; background: var(--c); opacity: .07; top: -50px; left: -50px; }
    .dv-role:hover { transform: translateY(-3px); box-shadow: 0 16px 32px -16px rgba(15,23,42,.25); border-color: color-mix(in srgb, var(--c) 35%, #eef2f7); }
    .dv-role-head { display: flex; align-items: flex-start; gap: .75rem; position: relative; z-index: 1; }
    .dv-role-avatar { width: 46px; height: 46px; border-radius: 14px; background: color-mix(in srgb, var(--c) 14%, #fff); color: var(--c); display: grid; place-items: center; font-weight: 800; font-size: 1.15rem; flex-shrink: 0; }
    .dv-role-name { font-weight: 800; font-size: 1rem; color: #0f172a; margin: 0; }
    .dv-role-slug { font-family: ui-monospace, Consolas, monospace; font-size: .72rem; color: #94a3b8; direction: ltr; display: inline-block; }
    .dv-role-desc { color: #64748b; font-size: .82rem; margin: .65rem 0 .9rem; min-height: 2.4em; position: relative; z-index: 1; }
    .dv-role-meta { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: .9rem; position: relative; z-index: 1; }
    .dv-chip { display: inline-flex; align-items: center; gap: .3rem; font-size: .74rem; font-weight: 700; padding: .25rem .6rem; border-radius: 999px; background: #f1f5f9; color: #475569; }
    .dv-chip-c { background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); }
    .dv-chip-lock { background: #fef3c7; color: #92400e; }
    .dv-progress { height: 6px; background: #f1f5f9; border-radius: 999px; overflow: hidden; margin-bottom: 1rem; position: relative; z-index: 1; }
    .dv-progress > i { display: block; height: 100%; background: var(--c); border-radius: 999px; transition: width .6s ease; }
    .dv-role-actions { display: flex; gap: .45rem; margin-top: auto; position: relative; z-index: 1; flex-wrap: wrap; }
    .dv-role-new {
        border: 2px dashed #c7d2fe; background: linear-gradient(180deg, #f5f3ff, #fff); display: flex; align-items: center; justify-content: center;
        flex-direction: column; gap: .5rem; text-decoration: none; color: #6366f1; min-height: 210px; font-weight: 800;
    }
    .dv-role-new::before, .dv-role-new::after { display: none; }
    .dv-role-new .plus { width: 52px; height: 52px; border-radius: 16px; display: grid; place-items: center; font-size: 1.8rem; background: #ede9fe; transition: transform .25s ease; }
    .dv-role-new:hover { color: #4f46e5; border-color: #a5b4fc; }
    .dv-role-new:hover .plus { transform: rotate(90deg) scale(1.05); }

    /* ---------- Table ---------- */
    .dv-table-wrap { overflow-x: auto; }
    .dv-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .87rem; }
    .dv-table th { text-align: right; font-size: .74rem; font-weight: 800; color: #64748b; background: #f8fafc; padding: .75rem 1rem; border-bottom: 1px solid #eef2f7; white-space: nowrap; }
    .dv-table td { padding: .7rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .dv-table tr:last-child td { border-bottom: 0; }
    .dv-table tbody tr { transition: background .15s; }
    .dv-table tbody tr:hover { background: #fafaff; }
    .dv-user { display: flex; align-items: center; gap: .65rem; }
    .dv-user-av { width: 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; font-weight: 800; font-size: .85rem; color: #fff; background: linear-gradient(135deg, #818cf8, #6366f1); flex-shrink: 0; }
    .dv-user b { display: block; font-weight: 700; color: #0f172a; font-size: .88rem; }
    .dv-user small { color: #94a3b8; font-size: .74rem; direction: ltr; display: inline-block; }
    .dv-role-pill { --c: #64748b; display: inline-flex; align-items: center; gap: .35rem; font-weight: 700; font-size: .76rem; padding: .25rem .65rem; border-radius: 999px; background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); white-space: nowrap; }
    .dv-role-pill::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--c); }
    .dv-select { appearance: none; -webkit-appearance: none; border: 1px solid #e2e8f0; border-radius: 10px; padding: .42rem 2rem .42rem .8rem; font-size: .82rem; font-family: inherit; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='3'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") no-repeat left .7rem center; min-width: 170px; cursor: pointer; transition: border-color .15s, box-shadow .15s; }
    .dv-select:focus, .dv-input:focus, .dv-textarea:focus { outline: none; border-color: #a5b4fc; box-shadow: 0 0 0 4px rgba(99,102,241,.12); }

    .dv-filters { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; padding: 1rem 1.1rem; border-bottom: 1px solid #eef2f7; }
    .dv-input, .dv-textarea { width: 100%; border: 1px solid #e2e8f0; border-radius: 12px; padding: .65rem .9rem; font-size: .9rem; font-family: inherit; background: #fff; transition: border-color .15s, box-shadow .15s; }
    .dv-search { position: relative; flex: 1; min-width: 220px; }
    .dv-search .dv-input { padding-right: 2.3rem; }
    .dv-search::before { content: '🔍'; position: absolute; right: .8rem; top: 50%; transform: translateY(-50%); font-size: .85rem; opacity: .6; }

    /* ---------- Alerts ---------- */
    .dv-alert { display: flex; align-items: center; gap: .7rem; padding: .85rem 1.1rem; border-radius: 14px; margin-bottom: 1.2rem; font-weight: 600; font-size: .9rem; animation: dvSlide .35s ease; }
    .dv-alert-ok { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
    .dv-alert-err { background: #fff1f2; color: #9f1239; border: 1px solid #fecdd3; }
    .dv-alert-warn { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    @keyframes dvSlide { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }

    /* ---------- Permission matrix ---------- */
    .dv-perm-groups { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 1rem; }
    .dv-perm-group { background: #fff; border: 1px solid #eef2f7; border-radius: 16px; overflow: hidden; transition: border-color .2s, box-shadow .2s; }
    .dv-perm-group.is-full { border-color: #c7d2fe; box-shadow: 0 8px 20px -14px rgba(99,102,241,.6); }
    .dv-perm-head { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .8rem 1rem; background: linear-gradient(180deg, #fafaff, #fff); border-bottom: 1px solid #f1f5f9; }
    .dv-perm-head h3 { margin: 0; font-size: .95rem; font-weight: 800; display: flex; align-items: center; gap: .45rem; color: #0f172a; }
    .dv-perm-count { font-size: .72rem; font-weight: 800; color: #6366f1; background: #eef2ff; padding: .15rem .5rem; border-radius: 999px; }
    .dv-perm-all { font-size: .74rem; font-weight: 700; color: #6366f1; background: none; border: 0; cursor: pointer; padding: .2rem .4rem; border-radius: 6px; font-family: inherit; }
    .dv-perm-all:hover { background: #eef2ff; }
    .dv-perm-item { display: flex; align-items: center; justify-content: space-between; gap: .8rem; padding: .7rem 1rem; border-bottom: 1px solid #f8fafc; cursor: pointer; transition: background .15s; margin: 0; }
    .dv-perm-item:last-child { border-bottom: 0; }
    .dv-perm-item:hover { background: #fafaff; }
    .dv-perm-item .t { font-size: .86rem; font-weight: 600; color: #334155; }
    .dv-perm-item code { display: block; font-size: .68rem; color: #94a3b8; direction: ltr; text-align: right; background: none; padding: 0; }

    /* Toggle switch */
    .dv-switch { position: relative; width: 42px; height: 24px; flex-shrink: 0; }
    .dv-switch input { position: absolute; opacity: 0; inset: 0; margin: 0; cursor: pointer; z-index: 2; }
    .dv-switch span { position: absolute; inset: 0; background: #e2e8f0; border-radius: 999px; transition: background .2s; }
    .dv-switch span::after { content: ''; position: absolute; top: 3px; right: 3px; width: 18px; height: 18px; background: #fff; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,.2); transition: transform .22s cubic-bezier(.4,1.4,.6,1); }
    .dv-switch input:checked + span { background: linear-gradient(135deg, #8b5cf6, #6366f1); }
    .dv-switch input:checked + span::after { transform: translateX(-18px); }
    .dv-switch input:disabled + span { opacity: .6; }
    .dv-switch input:focus-visible + span { box-shadow: 0 0 0 4px rgba(99,102,241,.2); }

    /* Form */
    .dv-form-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 1rem; }
    .dv-label { display: block; font-weight: 700; font-size: .84rem; color: #334155; margin-bottom: .4rem; }
    .dv-hint { font-size: .75rem; color: #94a3b8; margin-top: .3rem; }
    .dv-colors { display: flex; gap: .5rem; flex-wrap: wrap; }
    .dv-color { position: relative; width: 34px; height: 34px; }
    .dv-color input { position: absolute; opacity: 0; inset: 0; cursor: pointer; margin: 0; z-index: 2; }
    .dv-color span { position: absolute; inset: 0; border-radius: 10px; transition: transform .15s, box-shadow .15s; }
    .dv-color input:checked + span { transform: scale(1.1); box-shadow: 0 0 0 3px #fff, 0 0 0 5px currentColor; }
    .dv-color:hover span { transform: scale(1.08); }

    .dv-sticky-bar {
        position: sticky; bottom: 1rem; z-index: 20; margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
        background: rgba(15,23,42,.92); backdrop-filter: blur(10px); color: #fff; padding: .8rem 1rem; border-radius: 16px; box-shadow: 0 20px 40px -15px rgba(15,23,42,.6);
    }
    .dv-sticky-bar .sum { font-weight: 700; font-size: .9rem; }
    .dv-sticky-bar .sum b { color: #a5b4fc; font-size: 1.05rem; }
    .dv-tools { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
    .dv-tools .dv-select { min-width: 200px; }

    .dv-empty { text-align: center; padding: 2.5rem 1rem; color: #94a3b8; }
    .dv-empty .i { font-size: 2.2rem; margin-bottom: .4rem; }

    .dv-two { display: grid; grid-template-columns: 1fr 1.25fr; gap: 1.25rem; align-items: start; }

    /* TomSelect polish inside dev area */
    .dv-wrap .ts-wrapper.multi .ts-control { border-radius: 12px; border-color: #e2e8f0; padding: .45rem .6rem; min-height: 46px; }
    .dv-wrap .ts-wrapper.multi .ts-control > .item { background: #eef2ff; color: #4338ca; border: 0; border-radius: 8px; padding: .15rem .5rem; font-weight: 700; }
    .dv-wrap .ts-dropdown { border-radius: 12px; overflow: hidden; }

    @media (max-width: 900px) {
        .dv-form-grid, .dv-two { grid-template-columns: 1fr; }
        .dv-hero h1 { font-size: 1.35rem; }
        .dv-perm-groups { grid-template-columns: 1fr; }
    }
</style>
