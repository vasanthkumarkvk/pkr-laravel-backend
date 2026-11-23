<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PKR Estate Backend — Terminal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg:           #0a0e14;
            --bg-2:         #0d1117;
            --bg-3:         #161b22;
            --border:       #21262d;
            --border-mid:   #30363d;
            --text:         #c9d1d9;
            --text-dim:     #8b949e;
            --text-dimmer:  #484f58;
            --green:        #3fb950;
            --green-bright: #56d364;
            --green-dim:    #238636;
            --green-glow:   rgba(63, 185, 80, 0.15);
            --blue:         #58a6ff;
            --amber:        #d29922;
            --red:          #f85149;
            --purple:       #bc8cff;
            --cyan:         #39c5cf;
        }

        html, body {
            height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family: 'JetBrains Mono', 'SF Mono', Monaco, 'Cascadia Code', monospace;
            font-size: 13px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
            min-height: 100vh;
            background:
                radial-gradient(ellipse at top, rgba(63, 185, 80, 0.05) 0%, transparent 50%),
                radial-gradient(ellipse at bottom, rgba(88, 166, 255, 0.03) 0%, transparent 50%),
                var(--bg);
        }

        /* Terminal Window */
        .terminal {
            width: 100%;
            max-width: 880px;
            background: var(--bg-2);
            border: 1px solid var(--border-mid);
            border-radius: 10px;
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.02),
                0 24px 48px rgba(0, 0, 0, 0.6),
                0 0 80px rgba(63, 185, 80, 0.06);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px) scale(0.99); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Title bar */
        .terminal-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            background: var(--bg-3);
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .dot.red    { background: #ff5f57; }
        .dot.yellow { background: #febc2e; }
        .dot.green  { background: #28c840; }

        .terminal-title {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            color: var(--text-dim);
            font-weight: 500;
            letter-spacing: 0.02em;
        }

        .terminal-title .status-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--green);
            margin-right: 6px;
            vertical-align: middle;
            box-shadow: 0 0 8px var(--green);
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50%      { opacity: 0.4; }
        }

        /* Body */
        .terminal-body {
            padding: 24px 20px 32px;
            max-height: 75vh;
            overflow-y: auto;
        }

        .terminal-body::-webkit-scrollbar {
            width: 8px;
        }
        .terminal-body::-webkit-scrollbar-track {
            background: transparent;
        }
        .terminal-body::-webkit-scrollbar-thumb {
            background: var(--border-mid);
            border-radius: 4px;
        }
        .terminal-body::-webkit-scrollbar-thumb:hover {
            background: var(--text-dimmer);
        }

        /* ASCII Art */
        .ascii {
            color: var(--green);
            font-size: 10px;
            line-height: 1.1;
            white-space: pre;
            text-shadow: 0 0 12px var(--green-glow);
            margin-bottom: 20px;
            font-weight: 600;
        }

        @media (max-width: 640px) {
            .ascii { font-size: 6px; }
        }

        /* Lines */
        .line {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 13px;
            word-break: break-word;
        }

        .prompt {
            color: var(--green);
            font-weight: 600;
            flex-shrink: 0;
            user-select: none;
        }

        .prompt .path {
            color: var(--cyan);
        }

        .prompt .sep {
            color: var(--text-dimmer);
        }

        .line .cmd {
            color: var(--text);
        }

        .line .output {
            color: var(--text-dim);
        }

        /* Colors */
        .c-green  { color: var(--green); }
        .c-bright { color: var(--green-bright); }
        .c-blue   { color: var(--blue); }
        .c-amber  { color: var(--amber); }
        .c-red    { color: var(--red); }
        .c-purple { color: var(--purple); }
        .c-cyan   { color: var(--cyan); }
        .c-dim    { color: var(--text-dim); }
        .c-dimmer { color: var(--text-dimmer); }

        .bold { font-weight: 600; }

        /* Spacer */
        .spacer { height: 14px; }

        /* Section header */
        .section {
            color: var(--text-dimmer);
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin: 20px 0 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* Status table */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 8px;
            margin-top: 4px;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 12px;
            transition: all 0.15s;
        }

        .status-item:hover {
            background: rgba(255, 255, 255, 0.04);
            border-color: var(--border-mid);
        }

        .status-item .icon {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        .status-item .label {
            color: var(--text-dim);
            flex: 1;
        }

        .status-item .value {
            color: var(--green-bright);
            font-weight: 600;
        }

        /* Progress bar */
        .bar {
            display: inline-block;
            color: var(--green);
            letter-spacing: -1px;
            font-size: 11px;
        }

        .bar .empty { color: var(--border-mid); }

        /* Endpoints */
        .endpoint {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 5px 0;
            font-size: 12.5px;
        }

        .endpoint .method {
            font-weight: 700;
            font-size: 10.5px;
            padding: 1px 6px;
            border-radius: 3px;
            min-width: 44px;
            text-align: center;
            letter-spacing: 0.02em;
        }

        .method.get    { background: rgba(88, 166, 255, 0.15);  color: var(--blue);   }
        .method.post   { background: rgba(63, 185, 80, 0.15);   color: var(--green);  }
        .method.put    { background: rgba(210, 153, 34, 0.15);  color: var(--amber);  }
        .method.delete { background: rgba(248, 81, 73, 0.15);   color: var(--red);    }

        .endpoint .path {
            color: var(--text);
            flex: 1;
        }

        .endpoint .desc {
            color: var(--text-dimmer);
            font-size: 11.5px;
        }

        @media (max-width: 640px) {
            .endpoint .desc { display: none; }
        }

        /* Cursor */
        .cursor {
            display: inline-block;
            width: 8px;
            height: 15px;
            background: var(--green);
            vertical-align: middle;
            margin-left: 2px;
            animation: blink 1s step-end infinite;
            box-shadow: 0 0 8px var(--green);
        }

        @keyframes blink {
            0%, 100% { opacity: 1; }
            50%      { opacity: 0; }
        }

        /* Footer */
        .footer-line {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px dashed var(--border);
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 11.5px;
            color: var(--text-dimmer);
        }

        .footer-line a {
            color: var(--text-dim);
            text-decoration: none;
            transition: color 0.15s;
        }

        .footer-line a:hover {
            color: var(--green-bright);
        }

        /* Typing animation */
        .type-target {
            display: inline-block;
            overflow: hidden;
            white-space: nowrap;
            border-right: 2px solid var(--green);
            animation: typing 1.8s steps(40) 0.3s 1 normal both, blink-caret 0.75s step-end infinite;
        }

        @keyframes typing {
            from { width: 0; }
            to   { width: 100%; }
        }

        @keyframes blink-caret {
            from, to { border-color: transparent; }
            50%      { border-color: var(--green); }
        }

        /* Pulse animation for LIVE */
        .live {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--green-bright);
            font-weight: 600;
            font-size: 11.5px;
        }

        .live::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 8px var(--green);
            animation: pulse-dot 1.6s ease-in-out infinite;
        }

        /* Selection */
        ::selection {
            background: var(--green-dim);
            color: white;
        }
    </style>
</head>
<body>

    <div class="terminal">
        <!-- Title Bar -->
        <div class="terminal-bar">
            <span class="dot red"></span>
            <span class="dot yellow"></span>
            <span class="dot green"></span>
            <span class="terminal-title">
                <span class="status-dot"></span>
                pkr-estate — backend — zsh
            </span>
        </div>

        <!-- Body -->
        <div class="terminal-body">
            &ldrdhar;          &ldrdhar;        &ldrdhar;             &ldrdhar;        &ldrdhar;        &ldrdhar;            &ldrdhar;           &ldrdhar;

            <!-- ASCII Logo -->
            <div class="ascii">
    ____  __ __ ____     _____ __  _____ ____ ______
   / __ \/ //_// __ \   / ___// / / / ___//_  __// ____/
  / /_/ / ,&lt;  / /_/ /   \__ \/ /_/ /\__ \  / /  / /     
 / ____/ /| |/ _, _/   ___/ / __  /___/ / / /  / /___   
/_/   /_/ |_/_/ |_|   /____/_/ /_//____/ /_/   \____/   
            </div>

            <!-- Boot sequence -->
            <div class="line">
                <span class="prompt">→ <span class="path">~/pkr-estate</span> <span class="sep">$</span></span>
                <span class="cmd">php artisan serve --port=8002</span>
            </div>
            <div class="line">
                <span class="output">Laravel development server started:</span>
                <span class="c-blue">http://127.0.0.1:8002</span>
            </div>

            <div class="spacer"></div>

            <!-- Status -->
            <div class="line">
                <span class="prompt">→ <span class="path">~/pkr-estate</span> <span class="sep">$</span></span>
                <span class="cmd">php artisan pkr:status</span>
            </div>

            <div class="spacer"></div>

            <div class="section">System Health</div>

            <div class="status-grid">
                <div class="status-item">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #3fb950;">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="label">API</span>
                    <span class="value">200 OK</span>
                </div>
                <div class="status-item">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #3fb950;">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="label">Database</span>
                    <span class="value">Connected</span>
                </div>
                <div class="status-item">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #3fb950;">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="label">Redis Cache</span>
                    <span class="value">Active</span>
                </div>
                <div class="status-item">
                    <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: #3fb950;">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="label">Queue Worker</span>
                    <span class="value">Listening</span>
                </div>
            </div>

            <div class="spacer"></div>

            <!-- Metrics -->
            <div class="section">Metrics</div>

            <div class="line">
                <span class="output">Properties listed</span>
                <span class="c-bright bold">1,248</span>
                <span class="c-dimmer">·</span>
                <span class="output">Active leads</span>
                <span class="c-blue bold">342</span>
                <span class="c-dimmer">·</span>
                <span class="output">Agents</span>
                <span class="c-purple bold">56</span>
            </div>

            <div class="line">
                <span class="output">Uptime</span>
                <span class="c-green bold" id="uptime">00:00:00</span>
                <span class="c-dimmer">·</span>
                <span class="output">Response</span>
                <span class="c-green bold">112ms</span>
                <span class="c-dimmer">·</span>
                <span class="live">LIVE</span>
            </div>

            <div class="line">
                <span class="output">Storage</span>
                <span class="bar">████████████████████<span class="empty">░░░░░░░░</span></span>
                <span class="c-green bold">68%</span>
                <span class="c-dimmer">· 34.2 GB / 50 GB</span>
            </div>

            <div class="spacer"></div>

            <!-- Endpoints -->
            <div class="section">API Endpoints</div>

            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/api/admin/properties</span>
                <span class="desc">List all properties</span>
            </div>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/api/admin/leads</span>
                <span class="desc">Lead inquiries</span>
            </div>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/api/admin/agents</span>
                <span class="desc">Agent registry</span>
            </div>
            <div class="endpoint">
                <span class="method get">GET</span>
                <span class="path">/api/admin/dashboard/main-stats</span>
                <span class="desc">Dashboard metrics</span>
            </div>
            <div class="endpoint">
                <span class="method post">POST</span>
                <span class="path">/api/auth/login</span>
                <span class="desc">Admin authentication</span>
            </div>

            <div class="spacer"></div>

            <!-- Final prompt -->
            <div class="line">
                <span class="prompt">→ <span class="path">~/pkr-estate</span> <span class="sep">$</span></span>
                <span class="c-green type-target">curl -s https://pkr-estate.crazystory.in/api/health | jq</span>
            </div>
            <div class="line">
                <span class="output">{</span>
            </div>
            <div class="line">
                <span class="output">&nbsp;&nbsp;<span class="c-cyan">"status"</span>: <span class="c-green">"ok"</span>,</span>
            </div>
            <div class="line">
                <span class="output">&nbsp;&nbsp;<span class="c-cyan">"service"</span>: <span class="c-green">"pkr-estate-backend"</span>,</span>
            </div>
            <div class="line">
                <span class="output">&nbsp;&nbsp;<span class="c-cyan">"version"</span>: <span class="c-green">"{{ Illuminate\Foundation\Application::VERSION }}"</span>,</span>
            </div>
            <div class="line">
                <span class="output">&nbsp;&nbsp;<span class="c-cyan">"php"</span>: <span class="c-green">"{{ PHP_VERSION }}"</span>,</span>
            </div>
            <div class="line">
                <span class="output">&nbsp;&nbsp;<span class="c-cyan">"env"</span>: <span class="c-amber">"{{ app()->environment() }}"</span>,</span>
            </div>
            <div class="line">
                <span class="output">&nbsp;&nbsp;<span class="c-cyan">"uptime"</span>: <span class="c-green" id="uptime-json">"00:00:00"</span></span>
            </div>
            <div class="line">
                <span class="output">}</span>
            </div>

            <div class="spacer"></div>

            <div class="line">
                <span class="prompt">→ <span class="path">~/pkr-estate</span> <span class="sep">$</span></span>
                <span class="cursor"></span>
            </div>

            <!-- Footer -->
            <div class="footer-line">
                <span>pkr-estate-backend · {{ now()->format('D, d M Y · H:i') }}</span>
                <span>
                    <a href="/telescope">telescope</a> ·
                    <a href="/horizon">horizon</a> ·
                    <a href="/log-viewer">logs</a> ·
                    © {{ date('Y') }} PKR Estate
                </span>
            </div>

        </div>
    </div>

    <script>
        // Uptime ticker
        const startTime = new Date();
        function pad(n) { return String(n).padStart(2, '0'); }
        function updateUptime() {
            const now = new Date();
            const diffMs = now - startTime;
            const hrs  = Math.floor(diffMs / 3600000);
            const mins = Math.floor((diffMs % 3600000) / 60000);
            const secs = Math.floor((diffMs % 60000) / 1000);
            const str = `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
            const el = document.getElementById('uptime');
            if (el) el.textContent = str;
            const el2 = document.getElementById('uptime-json');
            if (el2) el2.textContent = `"${str}"`;
        }
        setInterval(updateUptime, 1000);
        updateUptime();

        // Console greeting
        console.log(
            '%cPKR Estate Backend%c  System operational · Terminal mode',
            'background:#238636;color:#fff;padding:4px 10px;border-radius:4px;font-weight:700;font-size:12px;font-family:monospace;',
            'color:#3fb950;font-size:12px;font-weight:600;font-family:monospace;'
        );
    </script>
</body>
</html>