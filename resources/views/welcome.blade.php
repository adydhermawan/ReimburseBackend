<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recashly API - System Status</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0F172A;
            --card-bg: #1E293B;
            --text-primary: #F8FAFC;
            --text-secondary: #94A3B8;
            --accent-color: #3B82F6;
            --accent-glow: rgba(59, 130, 246, 0.5);
            --success-color: #10B981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }

        /* Ambient Background */
        .ambient-light {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%);
            top: -200px;
            right: -200px;
            opacity: 0.15;
            pointer-events: none;
            z-index: 0;
        }

        .ambient-light-2 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #8B5CF6 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            opacity: 0.1;
            pointer-events: none;
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 10;
            text-align: center;
            max-width: 600px;
            padding: 2rem;
            animation: fadeIn 1s ease-out;
        }

        .logo-container {
            margin-bottom: 2rem;
            display: inline-block;
            position: relative;
        }

        .logo-img {
            width: 120px;
            height: 120px;
            object-fit: contain;
            filter: drop-shadow(0 0 20px var(--accent-glow));
            animation: pulse 3s infinite;
        }

        h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 3rem;
        }

        .status-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }

        .status-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .status-dot {
            width: 10px;
            height: 10px;
            background-color: var(--success-color);
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
            position: relative;
        }

        .status-dot::after {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border-radius: 50%;
            border: 1px solid var(--success-color);
            opacity: 0.5;
            animation: ripple 2s infinite;
        }

        .status-text {
            color: var(--text-primary);
            font-weight: 500;
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: var(--text-primary);
            color: var(--bg-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(255, 255, 255, 0.2);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-primary);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .footer {
            position: absolute;
            bottom: 2rem;
            color: rgba(148, 163, 184, 0.5);
            font-size: 0.8rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4); }
            70% { box-shadow: 0 0 0 20px rgba(59, 130, 246, 0); }
            100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }

        @keyframes ripple {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(2); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="ambient-light"></div>
    <div class="ambient-light-2"></div>

    <div class="container">
        <div class="logo-container">
            <img src="/logo-white.png" alt="Recashly Logo" class="logo-img" />
        </div>
        
        <h1>Recashly API</h1>
        <p class="subtitle">Secure Operational Environment</p>

        <div class="status-card">
            <div class="status-dot"></div>
            <span class="status-text">System All Systems Operational</span>
        </div>

        <div class="actions">
            <a href="/admin" class="btn btn-primary">
                Access Admin Panel
            </a>
            <!-- Optional: Link to docs if available, or just keeping it clean -->
            <a href="#" onclick="verifyConnection(event)" class="btn btn-secondary">
                Verify Connection
            </a>
        </div>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Recashly API System. v1.0.0
    </div>
    <!-- Connection Details Modal/Container -->
    <div id="connection-details" style="display: none; margin-top: 1rem; text-align: left; background: rgba(30, 41, 59, 0.8); padding: 1rem; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.05);">
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem;">
            <span style="color: var(--text-secondary);">API Endpoint</span>
            <span id="status-api" style="color: var(--text-primary);">...</span>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 0.5rem;">
            <span style="color: var(--text-secondary);">Database (TiDB)</span>
            <span id="status-db" style="color: var(--text-primary);">...</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span style="color: var(--text-secondary);">Cloudinary Assets</span>
            <span id="status-cloudinary" style="color: var(--text-primary);">...</span>
        </div>
    </div>

    <script>
        function verifyConnection(event) {
            event.preventDefault();
            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            const detailsDiv = document.getElementById('connection-details');
            const mainStatusText = document.querySelector('.status-text');
            const statusDot = document.querySelector('.status-dot');
            
            // Reset UI
            btn.innerHTML = 'Verifying...';
            btn.style.opacity = '0.7';
            btn.style.pointerEvents = 'none';
            detailsDiv.style.display = 'none';
            
            // Insert details div after status card if not already moved
            const statusCard = document.querySelector('.status-card');
            statusCard.parentNode.insertBefore(detailsDiv, statusCard.nextSibling);

            fetch('/api/health-check')
                .then(response => response.json())
                .then(data => {
                    // Update details
                    document.getElementById('status-api').innerHTML = data.api ? '<span style="color: #10B981;">✓ Connected</span>' : '<span style="color: #EF4444;">✗ Failed</span>';
                    document.getElementById('status-db').innerHTML = data.database ? '<span style="color: #10B981;">✓ Connected</span>' : '<span style="color: #EF4444;">✗ Failed</span>';
                    document.getElementById('status-cloudinary').innerHTML = data.cloudinary ? '<span style="color: #10B981;">✓ Configured</span>' : '<span style="color: #EF4444;">✗ Not Configured</span>';
                    
                    detailsDiv.style.display = 'block';
                    
                    // Update main status if any issues
                    if (!data.api || !data.database || !data.cloudinary) {
                         mainStatusText.textContent = data.message || "System Issues Detected";
                         statusDot.style.backgroundColor = '#EF4444';
                         statusDot.style.boxShadow = '0 0 10px rgba(239, 68, 68, 0.5)';
                         // Update ripple color via css variable manipulation or class if needed, checking style directly is easiest for inline
                         // For now just dot color is reliable
                    } else {
                         mainStatusText.textContent = "All Systems Operational";
                         statusDot.style.backgroundColor = '#10B981';
                         statusDot.style.boxShadow = '0 0 10px rgba(16, 185, 129, 0.5)';
                    }
                })
                .catch(err => {
                    console.error(err);
                    mainStatusText.textContent = "Connection Failed";
                    statusDot.style.backgroundColor = '#EF4444';
                    alert('Failed to contact server');
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.style.opacity = '1';
                    btn.style.pointerEvents = 'auto';
                });
        }
    </script>
</body>
</html>
