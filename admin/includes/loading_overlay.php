  <style>
        /* Page content styles */
        /* body {
            min-height: 200vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        
        .form-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
         */
        /* .content-section {
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        } */
        
        /* Loading Overlay Styles */
        .loading-overlay {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            min-width: 100vw !important;
            min-height: 100vh !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px) !important;
            -webkit-backdrop-filter: blur(10px) !important;
            z-index: 99999 !important;
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            transition: opacity 0.3s ease !important;
        }
        
        .loading-overlay.active {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Loading Container */
        .loading-container {
            text-align: center;
            padding: 40px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            animation: floatIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        @keyframes floatIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Option 1: Modern Spinner */
        .spinner-modern {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            position: relative;
        }
        
        .spinner-modern .spinner-circle {
            width: 100%;
            height: 100%;
            border: 4px solid transparent;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }
        
        .spinner-modern .spinner-inner-circle {
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 4px solid transparent;
            border-top-color: #8b5cf6;
            border-radius: 50%;
            animation: spin 0.8s cubic-bezier(0.5, 0, 0.5, 1) infinite reverse;
        }
        
        /* Option 2: Pulsing Dots */
        .pulse-loader {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            height: 60px;
        }
        
        .pulse-dot {
            width: 18px;
            height: 18px;
            background: #4f46e5;
            border-radius: 50%;
            animation: pulse 1.4s ease-in-out infinite;
        }
        
        .pulse-dot:nth-child(1) { animation-delay: -0.32s; }
        .pulse-dot:nth-child(2) { animation-delay: -0.16s; }
        
        /* Option 3: Morphing Shapes */
        .morph-loader {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            position: relative;
        }
        
        .morph-shape {
            position: absolute;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #4f46e5, #8b5cf6);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation: morph 3s ease-in-out infinite;
            filter: blur(2px);
        }
        
        .morph-shape:nth-child(2) {
            background: linear-gradient(45deg, #8b5cf6, #a78bfa);
            animation-delay: -1s;
            border-radius: 70% 30% 30% 70% / 70% 70% 30% 30%;
        }
        
        /* Option 4: Bouncing Bars */
        .bar-loader {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 6px;
            height: 60px;
            margin-bottom: 25px;
        }
        
        .bar {
            width: 10px;
            background: #4f46e5;
            border-radius: 4px;
            animation: bounce 1.2s ease-in-out infinite;
        }
        
        .bar:nth-child(1) { height: 30px; animation-delay: -0.24s; }
        .bar:nth-child(2) { height: 40px; animation-delay: -0.12s; }
        .bar:nth-child(3) { height: 50px; animation-delay: 0s; }
        .bar:nth-child(4) { height: 40px; animation-delay: 0.12s; }
        .bar:nth-child(5) { height: 30px; animation-delay: 0.24s; }
        
        /* Option 5: Orbital Loader */
        .orbital-loader {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            position: relative;
        }
        
        .orbital-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 20px;
            height: 20px;
            background: #4f46e5;
            border-radius: 50%;
        }
        
        .orbital-dot {
            position: absolute;
            width: 16px;
            height: 16px;
            background: #8b5cf6;
            border-radius: 50%;
            animation: orbit 2s linear infinite;
        }
        
        .orbital-dot:nth-child(2) { animation-delay: -0.4s; background: #a78bfa; }
        .orbital-dot:nth-child(3) { animation-delay: -0.8s; background: #c4b5fd; }
        .orbital-dot:nth-child(4) { animation-delay: -1.2s; background: #ddd6fe; }
        .orbital-dot:nth-child(5) { animation-delay: -1.6s; background: #ede9fe; }
        
        /* Loading Text */
        .loading-text {
            font-size: 1.2rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }
        
        .loading-subtext {
            color: #6b7280;
            font-size: 0.95rem;
            max-width: 300px;
            margin: 0 auto;
            line-height: 1.5;
        }
        
        /* Progress Bar */
        .loading-progress {
            width: 200px;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            margin: 20px auto 0;
            overflow: hidden;
        }
        
        .loading-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #4f46e5, #8b5cf6);
            width: 0%;
            animation: progress 2s ease-in-out infinite;
            border-radius: 2px;
        }
        
        /* Animations */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(0.5); opacity: 0.5; }
        }
        
        @keyframes morph {
            0%, 100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
            25% { border-radius: 70% 30% 30% 70% / 70% 70% 30% 30%; }
            50% { border-radius: 40% 60% 60% 40% / 40% 40% 60% 60%; }
            75% { border-radius: 60% 40% 40% 60% / 60% 60% 40% 40%; }
        }
        
        @keyframes bounce {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(0.3); }
        }
        
        @keyframes orbit {
            0% { transform: rotate(0deg) translateX(30px) rotate(0deg); }
            100% { transform: rotate(360deg) translateX(30px) rotate(-360deg); }
        }
        
        @keyframes progress {
            0% { width: 0%; transform: translateX(-100%); }
            50% { width: 70%; }
            100% { width: 100%; transform: translateX(100%); }
        }
        
        /* Loading Type Selector */
        .loader-selector {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .loader-btn {
            padding: 8px 16px;
            background: #f3f4f6;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            color: #4b5563;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .loader-btn:hover {
            background: #e5e7eb;
            border-color: #d1d5db;
        }
        
        .loader-btn.active {
            background: #4f46e5;
            color: white;
            border-color: #4f46e5;
        }
        
        /* Form Styling */
        /* .form-control:focus {
            border-color: #8b5cf6;
            box-shadow: 0 0 0 0.2rem rgba(139, 92, 246, 0.25);
        } */
        
        /* .btn-submit {
            background: linear-gradient(135deg, #4f46e5, #8b5cf6);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        } */
    </style>
 
 
 <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-container">
            <!-- Spinner Loader (Default) -->
            <div class="loader-content" id="spinnerLoader">
                <div class="spinner-modern">
                    <div class="spinner-circle"></div>
                    <div class="spinner-inner-circle"></div>
                </div>
                <div class="loading-text">Processing your request</div>
                <div class="loading-subtext">Please wait while we save your information</div>
                <div class="loading-progress">
                    <div class="loading-progress-bar"></div>
                </div>
            </div>
            
            <!-- Pulse Dots Loader -->
            <div class="loader-content" id="pulseLoader" style="display: none;">
                <div class="pulse-loader">
                    <div class="pulse-dot"></div>
                    <div class="pulse-dot"></div>
                    <div class="pulse-dot"></div>
                </div>
                <div class="loading-text">Analyzing data</div>
                <div class="loading-subtext">Preparing your submission for processing</div>
            </div>
            
            <!-- Morphing Loader -->
            <div class="loader-content" id="morphLoader" style="display: none;">
                <div class="morph-loader">
                    <div class="morph-shape"></div>
                    <div class="morph-shape"></div>
                </div>
                <div class="loading-text">Transforming data</div>
                <div class="loading-subtext">Applying advanced algorithms to your submission</div>
            </div>
            
            <!-- Bouncing Bars Loader -->
            <div class="loader-content" id="barsLoader" style="display: none;">
                <div class="bar-loader">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
                <div class="loading-text">Validating information</div>
                <div class="loading-subtext">Checking all fields for accuracy and completeness</div>
            </div>
            
            <!-- Orbital Loader -->
            <div class="loader-content" id="orbitalLoader" style="display: none;">
                <div class="orbital-loader">
                    <div class="orbital-center"></div>
                    <div class="orbital-dot"></div>
                    <div class="orbital-dot"></div>
                    <div class="orbital-dot"></div>
                    <div class="orbital-dot"></div>
                    <div class="orbital-dot"></div>
                </div>
                <div class="loading-text">Orchestrating processes</div>
                <div class="loading-subtext">Coordinating multiple systems to handle your request</div>
            </div>
        </div>
    </div>