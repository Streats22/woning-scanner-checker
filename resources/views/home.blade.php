<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>De Huur Radar</title>
    <style>
        :root {
            --bg: #0a101d;
            --card: #111827;
            --text: #e8eef4;
            --muted: #8b9aab;
            --accent: #60a5fa;
            --border: #2d3a4d;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }
        .wrap { max-width: 42rem; margin: 0 auto; padding: 2rem 1.25rem 4rem; }
        h1 { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.5rem; }
        .sub { color: var(--muted); font-size: 0.95rem; margin-bottom: 1.5rem; }
        label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; }
        textarea {
            width: 100%;
            min-height: 12rem;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            font-size: 0.9375rem;
            resize: vertical;
        }
        textarea:focus { outline: 2px solid var(--accent); outline-offset: 2px; }
        .btn {
            margin-top: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.65rem 1.25rem;
            font-size: 0.9375rem;
            font-weight: 600;
            color: #fff;
            background: var(--accent);
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
        }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn:hover:not(:disabled) { filter: brightness(1.08); }
        .out {
            margin-top: 2rem;
            padding: 1.25rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border);
            background: var(--card);
            display: none;
        }
        .out.visible { display: block; }
        .score-row { display: flex; align-items: baseline; gap: 0.75rem; flex-wrap: wrap; }
        .score-val { font-size: 2.25rem; font-weight: 700; line-height: 1; }
        .score-label { font-size: 0.875rem; color: var(--muted); }
        .pill {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .c-green { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
        .c-orange { background: rgba(251, 146, 60, 0.2); color: #fb923c; }
        .c-red { background: rgba(239, 68, 68, 0.2); color: #f87171; }
        h2 { font-size: 1rem; font-weight: 600; margin: 1.25rem 0 0.5rem; }
        ul { margin: 0; padding-left: 1.25rem; }
        li { margin: 0.35rem 0; }
        .market { font-size: 0.9375rem; color: var(--muted); }
        .market strong { color: var(--text); }
        .summary { font-size: 0.9375rem; }
        .err { color: #f87171; font-size: 0.875rem; margin-top: 0.75rem; }
        .share {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border);
            font-size: 0.875rem;
        }
        .share a { color: var(--accent); word-break: break-all; }
    </style>
</head>
<body>
    <div class="wrap">
        <h1>De Huur Radar</h1>
        <p class="sub">Plak een advertentie. Je krijgt een risicoscore op basis van duidelijke regels — geen black box.</p>

        <form id="form" action="#" method="post">
            <label for="text">Advertentietekst</label>
            <textarea id="text" name="text" required placeholder="Bijv. € 850 in Amsterdam, alleen WhatsApp…"></textarea>
            <button type="submit" class="btn" id="btn">Analyze</button>
            <p class="err" id="err" style="display:none;"></p>
        </form>

        <div class="out" id="out">
            <div class="score-row">
                <span class="score-val" id="scoreNum">—</span>
                <span class="score-label">scam-risico (0–100)</span>
                <span class="pill" id="scorePill">—</span>
            </div>

            <h2>Rode vlaggen</h2>
            <ul id="flags"></ul>

            <h2>Prijsvergelijking</h2>
            <p class="market" id="market"></p>

            <h2>Samenvatting</h2>
            <p class="summary" id="summary"></p>

            <div class="share" id="share" style="display:none;">
                <strong>Rapport</strong><br>
                <a href="#" id="reportPdfLink" target="_blank" rel="noopener" style="display:none;">Download PDF</a>
                <span id="reportPdfSep" style="display:none;"> · </span>
                <a href="#" id="reportLink" target="_blank" rel="noopener"></a>
            </div>
        </div>
    </div>
    <script>
        const form = document.getElementById('form');
        const btn = document.getElementById('btn');
        const err = document.getElementById('err');
        const out = document.getElementById('out');
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function scoreClass(n) {
            if (n <= 30) return 'c-green';
            if (n <= 60) return 'c-orange';
            return 'c-red';
        }
        function scoreLabel(n) {
            if (n <= 30) return 'Laag risico';
            if (n <= 60) return 'Matig risico';
            return 'Hoog risico';
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            err.style.display = 'none';
            btn.disabled = true;
            const text = document.getElementById('text').value;
            try {
                const res = await fetch('{{ url('/analyze') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ text }),
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    const msg = data.errors?.text?.[0] || data.message || (data.errors && JSON.stringify(data.errors)) || 'Analyse mislukt';
                    throw new Error(msg);
                }
                const score = data.score;
                document.getElementById('scoreNum').textContent = score;
                const pill = document.getElementById('scorePill');
                pill.textContent = scoreLabel(score);
                pill.className = 'pill ' + scoreClass(score);

                const ul = document.getElementById('flags');
                ul.innerHTML = '';
                (data.flags || []).forEach((f) => {
                    const li = document.createElement('li');
                    li.textContent = f;
                    ul.appendChild(li);
                });
                if (!data.flags || data.flags.length === 0) {
                    const li = document.createElement('li');
                    li.textContent = 'Geen automatisch gedetecteerde rode vlaggen.';
                    ul.appendChild(li);
                }

                const m = data.market || {};
                const diff = m.difference_percent;
                document.getElementById('market').innerHTML =
                    'Geschatte marktbenchmark (huur): <strong>€ ' + m.average + '</strong> per maand.' +
                    (diff != null ? ' Jouw prijs wijkt <strong>' + diff + '%</strong> af t.o.v. die benchmark.' : ' Geen prijs in de tekst om te vergelijken.');

                document.getElementById('summary').textContent = data.summary || '';

                const share = document.getElementById('share');
                const link = document.getElementById('reportLink');
                const pdfLink = document.getElementById('reportPdfLink');
                const pdfSep = document.getElementById('reportPdfSep');
                if (data.report_url || data.report_pdf_url) {
                    share.style.display = 'block';
                    if (data.report_pdf_url) {
                        pdfLink.style.display = 'inline';
                        pdfLink.href = data.report_pdf_url;
                        pdfSep.style.display = 'inline';
                    } else {
                        pdfLink.style.display = 'none';
                        pdfSep.style.display = 'none';
                    }
                    if (data.report_url) {
                        link.href = data.report_url;
                        link.textContent = 'Webversie (tijdelijk)';
                    } else {
                        link.href = '#';
                        link.textContent = '';
                    }
                } else {
                    share.style.display = 'none';
                }

                out.classList.add('visible');
            } catch (x) {
                err.textContent = x.message || 'Er ging iets mis.';
                err.style.display = 'block';
            } finally {
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
