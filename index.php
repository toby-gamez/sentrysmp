<!doctype html>
<html lang="cs">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>SentrySMP - Your journey starts here</title>
        <link
            href="https://unpkg.com/aos@2.3.1/dist/aos.css"
            rel="stylesheet"
        />
        <style>
            @import url("https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css");
            @import url("https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap");
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            html {
                scroll-behavior: smooth;
            }

            body {
                font-family: "Outfit", sans-serif;
                background: #090909;
                color: #ffffff;
                overflow-x: hidden;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
            }

            /* Hero Section */
            .hero {
                min-height: 100vh;
                display: flex;
                align-items: center;
                position: relative;
                overflow: hidden;
                background: linear-gradient(
                    135deg,
                    #090909 0%,
                    #1a0b0b 50%,
                    #2d1414 100%
                );
            }

            .hero::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="%23dc3545" stroke-width="0.08" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
                animation: moveGrid 20s linear infinite;
            }

            @keyframes moveGrid {
                0% {
                    transform: translate(0, 0);
                }
                100% {
                    transform: translate(10px, 10px);
                }
            }

            .hero-content {
                position: relative;
                z-index: 2;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 80px;
                align-items: center;
            }

            .hero-left {
                text-align: left;
                animation: slideInLeft 1s ease-out;
                transition: transform 0.3s ease;
            }

            .hero-left:hover {
                transform: translateX(10px);
            }

            @keyframes slideInLeft {
                from {
                    opacity: 0;
                    transform: translateX(-50px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            .hero-slogan {
                font-size: clamp(3rem, 6vw, 4.5rem);
                font-weight: 800;
                margin-bottom: 30px;
                background: linear-gradient(45deg, #ffffff, #dc3545, #ff6b6b);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                line-height: 1.1;
                animation: glow 3s ease-in-out infinite alternate;
                cursor: default;
                transition: all 0.3s ease;
            }

            .hero-slogan:hover {
                transform: scale(1.02);
                filter: drop-shadow(0 0 50px rgba(220, 53, 69, 0.8));
            }

            @keyframes glow {
                from {
                    filter: drop-shadow(0 0 20px rgba(220, 53, 69, 0.3));
                }
                to {
                    filter: drop-shadow(0 0 40px rgba(220, 53, 69, 0.6));
                }
            }

            .hero-text {
                font-size: 1.2rem;
                margin-bottom: 40px;
                color: #b8c5d6;
                line-height: 1.6;
                max-width: 500px;
                animation: fadeInUp 1s ease-out 0.3s both;
                transition: all 0.3s ease;
                cursor: default;
            }

            .hero-text:hover {
                color: #ffffff;
                transform: translateY(-2px);
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .server-ip {
                background: rgba(220, 53, 69, 0.1);
                border: 2px solid #dc3545;
                padding: 15px 25px;
                border-radius: 12px;
                font-size: 1.1rem;
                font-weight: 600;
                margin-bottom: 30px;
                display: inline-block;
                transition: all 0.3s ease;
                cursor: pointer;
                color: #dc3545;
                animation: fadeInUp 1s ease-out 0.5s both;
                position: relative;
                overflow: hidden;
            }

            .server-ip::before {
                content: "";
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(
                    90deg,
                    transparent,
                    rgba(255, 255, 255, 0.1),
                    transparent
                );
                transition: left 0.5s;
            }

            .server-ip:hover::before {
                left: 100%;
            }

            .server-ip:hover {
                background: rgba(220, 53, 69, 0.2);
                transform: translateY(-5px) scale(1.05);
                box-shadow: 0 15px 35px rgba(220, 53, 69, 0.5);
                border-color: #ff4757;
            }

            .cta-buttons {
                display: flex;
                gap: 20px;
                flex-wrap: wrap;
                animation: fadeInUp 1s ease-out 0.7s both;
            }

            .btn {
                padding: 16px 32px;
                border: none;
                border-radius: 12px;
                font-size: 1.1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                text-decoration: none;
                display: inline-block;
                position: relative;
                overflow: hidden;
            }

            .btn::before {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                background: rgba(255, 255, 255, 0.1);
                transition: all 0.6s ease;
                border-radius: 50%;
                transform: translate(-50%, -50%);
            }

            .btn:hover::before {
                width: 300px;
                height: 300px;
            }

            .btn-primary {
                background: linear-gradient(45deg, #dc3545, #ff4757);
                color: white;
                box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
            }

            .btn-primary:hover {
                transform: translateY(-8px) scale(1.1);
                box-shadow: 0 20px 40px rgba(220, 53, 69, 0.6);
                background: linear-gradient(45deg, #ff4757, #dc3545);
            }

            .btn-secondary {
                background: transparent;
                color: #ffffff;
                border: 2px solid rgba(255, 255, 255, 0.3);
            }

            .btn-secondary:hover {
                background: rgba(255, 255, 255, 0.1);
                border-color: #ffffff;
                transform: translateY(-8px) scale(1.1);
                box-shadow: 0 20px 40px rgba(255, 255, 255, 0.2);
            }

            .hero-right {
                position: relative;
                display: flex;
                justify-content: center;
                align-items: center;
                animation: slideInRight 1s ease-out 0.3s both;
            }

            @keyframes slideInRight {
                from {
                    opacity: 0;
                    transform: translateX(50px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            .hero-image {
                width: 400px;
                height: 400px;
                background: linear-gradient(
                    45deg,
                    rgba(220, 53, 69, 0.1),
                    rgba(255, 71, 87, 0.1)
                );
                border-radius: 20px;
                border: 1px solid rgba(220, 53, 69, 0.3);
                backdrop-filter: blur(10px);
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                overflow: hidden;
                animation: floatStatic 6s ease-in-out infinite;
                transition: all 0.4s ease;
                cursor: pointer;
            }

            .hero-image:hover {
                transform: translateY(-20px) scale(1.05) rotate(2deg);
                border-color: #dc3545;
                box-shadow: 0 30px 60px rgba(220, 53, 69, 0.4);
                background: linear-gradient(
                    45deg,
                    rgba(220, 53, 69, 0.2),
                    rgba(255, 71, 87, 0.2)
                );
            }

            @keyframes floatStatic {
                0%,
                100% {
                    transform: translateY(0px);
                }
                50% {
                    transform: translateY(-10px);
                }
            }

            .hero-image::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="heroGrid" width="8" height="8" patternUnits="userSpaceOnUse"><path d="M 8 0 L 0 0 0 8" fill="none" stroke="%23dc3545" stroke-width="0.5" opacity="0.2"/></pattern></defs><rect width="100" height="100" fill="url(%23heroGrid)"/></svg>');
                animation: moveHeroGrid 15s linear infinite;
            }

            @keyframes moveHeroGrid {
                0% {
                    transform: translate(0, 0);
                }
                100% {
                    transform: translate(8px, 8px);
                }
            }

            .hero-image-content {
                position: relative;
                z-index: 2;
                text-align: center;
                font-size: 1.2rem;
                color: #dc3545;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .hero-image:hover .hero-image-content {
                transform: scale(1.1);
                color: #ff4757;
            }

            .scroll-indicator {
                position: absolute;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%);
                color: rgba(220, 53, 69, 0.8);
                font-size: 2rem;
                animation: bounce 2s infinite;
                cursor: pointer;
                z-index: 10;
                transition: all 0.3s ease;
            }

            .scroll-indicator:hover {
                color: #dc3545;
                transform: translateX(-50%) scale(1.2);
            }

            .scroll-indicator::before {
                content: "⌄";
                font-size: 2.5rem;
                font-weight: bold;
            }

            @keyframes bounce {
                0%,
                20%,
                50%,
                80%,
                100% {
                    transform: translateX(-50%) translateY(0);
                }
                40% {
                    transform: translateX(-50%) translateY(-10px);
                }
                60% {
                    transform: translateX(-50%) translateY(-5px);
                }
            }

            /* Why Play Section */
            .why-play {
                padding: 100px 0;
                background: #0f0f0f;
            }

            .section-title {
                text-align: center;
                font-size: 3rem;
                font-weight: 700;
                margin-bottom: 60px;
                background: linear-gradient(45deg, #ffffff, #dc3545);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                transition: all 0.3s ease;
                cursor: default;
            }

            .section-title:hover {
                transform: scale(1.05);
                filter: drop-shadow(0 0 30px rgba(220, 53, 69, 0.5));
            }

            .features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 30px;
                margin-top: 60px;
            }

            .feature-card {
                background: rgba(255, 255, 255, 0.02);
                border: 1px solid rgba(220, 53, 69, 0.2);
                border-radius: 15px;
                padding: 40px 30px;
                text-align: center;
                transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
                backdrop-filter: blur(10px);
                position: relative;
                overflow: hidden;
            }

            .feature-card::before {
                content: "";
                position: absolute;
                top: -50%;
                left: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(
                    circle,
                    rgba(220, 53, 69, 0.1) 0%,
                    transparent 70%
                );
                opacity: 0;
                transition: opacity 0.4s ease;
            }

            .feature-card:hover::before {
                opacity: 1;
            }

            .feature-card:hover {
                transform: translateY(-20px) scale(1.2);
                border-color: #dc3545;
                box-shadow: 0 30px 60px rgba(220, 53, 69, 0.4);
                background: rgba(220, 53, 69, 0.08);
            }

            .feature-icon {
                font-size: 3rem;
                margin-bottom: 20px;
                display: block;
                transition: all 0.4s ease;
                position: relative;
                z-index: 2;
                color: #dc3545;
            }

            .feature-card:hover .feature-icon {
                transform: scale(1.3) rotate(10deg);
                filter: drop-shadow(0 0 20px rgba(220, 53, 69, 0.6));
            }

            .feature-card h3 {
                font-size: 1.5rem;
                margin-bottom: 15px;
                color: #dc3545;
                transition: all 0.3s ease;
                position: relative;
                z-index: 2;
            }

            .feature-card:hover h3 {
                color: #ff4757;
                transform: translateY(-3px);
            }

            .feature-card p {
                color: #b8c5d6;
                line-height: 1.6;
                transition: all 0.3s ease;
                position: relative;
                z-index: 2;
            }

            .feature-card:hover p {
                color: #ffffff;
                transform: translateY(-2px);
            }

            /* Stats Section */
            .stats {
                padding: 100px 0;
                background: linear-gradient(
                    45deg,
                    rgba(220, 53, 69, 0.03),
                    rgba(255, 71, 87, 0.03)
                );
                position: relative;
            }

            .stats::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="statsGrid" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M 20 0 L 0 0 0 20" fill="none" stroke="%23dc3545" stroke-width="0.1" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23statsGrid)"/></svg>');
            }

            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 40px;
                position: relative;
                z-index: 2;
            }

            .stat-card {
                text-align: center;
                padding: 40px 30px;
                background: rgba(255, 255, 255, 0.02);
                border-radius: 15px;
                border: 1px solid rgba(220, 53, 69, 0.2);
                backdrop-filter: blur(10px);
                transition: all 0.4s ease;
                position: relative;
                overflow: hidden;
            }

            .stat-card::before {
                content: "";
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(
                    90deg,
                    transparent,
                    rgba(220, 53, 69, 0.1),
                    transparent
                );
                transition: left 0.6s ease;
            }

            .stat-card:hover::before {
                left: 100%;
            }

            .stat-card:hover {
                transform: translateY(-15px) scale(1.05);
                border-color: #dc3545;
                box-shadow: 0 25px 50px rgba(220, 53, 69, 0.3);
                background: rgba(220, 53, 69, 0.05);
            }

            .stat-number {
                font-size: 3rem;
                font-weight: 800;
                color: #dc3545;
                margin-bottom: 10px;
                display: block;
                transition: all 0.4s ease;
                position: relative;
                z-index: 2;
            }

            .stat-card:hover .stat-number {
                transform: scale(1.2) rotateY(10deg);
                color: #ff4757;
                filter: drop-shadow(0 0 20px rgba(220, 53, 69, 0.6));
            }

            .stat-label {
                font-size: 1rem;
                color: #b8c5d6;
                text-transform: uppercase;
                letter-spacing: 1px;
                transition: all 0.3s ease;
                position: relative;
                z-index: 2;
            }

            .stat-card:hover .stat-label {
                color: #ffffff;
                transform: translateY(-3px);
            }

            /* FAQ Section */
            .faq {
                padding: 100px 0;
                background: #0a0a0a;
            }

            .faq-item {
                background: rgba(255, 255, 255, 0.02);
                border: 1px solid rgba(220, 53, 69, 0.2);
                border-radius: 12px;
                margin-bottom: 20px;
                overflow: hidden;
                transition: all 0.4s ease;
                position: relative;
            }

            .faq-item::before {
                content: "";
                position: absolute;
                left: 0;
                top: 0;
                width: 0;
                height: 100%;
                background: linear-gradient(90deg, #dc3545, #ff4757);
                transition: width 0.4s ease;
            }

            .faq-item:hover::before {
                width: 4px;
            }

            .faq-item:hover {
                border-color: #dc3545;
                transform: translateX(10px) scale(1.02);
                box-shadow: 0 10px 30px rgba(220, 53, 69, 0.2);
            }

            .faq-question {
                padding: 25px 30px;
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-weight: 600;
                font-size: 1.1rem;
                transition: all 0.3s ease;
                position: relative;
                z-index: 2;
            }

            .faq-question:hover {
                background: rgba(220, 53, 69, 0.1);
                transform: translateX(5px);
            }

            .faq-answer {
                padding: 0 30px 0;
                color: #b8c5d6;
                line-height: 1.6;
                max-height: 0;
                overflow: hidden;
                transition: all 0.4s ease;
                position: relative;
                z-index: 2;
            }

            .faq-item.active .faq-answer {
                max-height: 200px;
                padding: 20px 30px 25px;
            }

            .faq-toggle {
                transition: all 0.4s ease;
                color: #dc3545;
                font-size: 1.2rem;
            }

            .faq-item.active .faq-toggle {
                transform: rotate(180deg) scale(1.2);
                color: #ff4757;
            }

            /* Shop Section */
            .shop {
                padding: 100px 0;
                background: rgba(255, 165, 0, 0.03);
                text-align: center;
                position: relative;
            }

            .shop::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: radial-gradient(
                    circle at 50% 50%,
                    rgba(255, 165, 0, 0.1) 0%,
                    transparent 70%
                );
            }

            .shop-content {
                max-width: 600px;
                margin: 0 auto;
                position: relative;
                z-index: 2;
                transition: transform 0.3s ease;
            }

            .shop-content:hover {
                transform: translateY(-10px) !important;
            }

            .shop h2 {
                font-size: 2.5rem;
                margin-bottom: 20px;
                color: #ff8c00;
                transition: all 0.3s ease !important;
                cursor: default;
            }

            .shop-content:hover h2 {
                color: #ff7700 !important;
                transform: scale(1.05) !important;
            }

            .shop p {
                font-size: 1.2rem;
                color: #b8c5d6;
                margin-bottom: 40px;
                line-height: 1.6;
                transition: all 0.3s ease !important;
            }

            .shop-content:hover p {
                color: #ffffff !important;
            }

            .shop-btn {
                background: linear-gradient(45deg, #ff8c00, #ff7700);
                color: white;
                padding: 18px 40px;
                border-radius: 12px;
                text-decoration: none;
                font-size: 1.1rem;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
                box-shadow: 0 4px 15px rgba(255, 140, 0, 0.3);
                position: relative;
                overflow: hidden;
            }

            .shop-btn::before {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                background: rgba(255, 255, 255, 0.1);
                transition: all 0.6s ease !important;
                border-radius: 50%;
                transform: translate(-50%, -50%);
            }

            .shop-btn:hover::before {
                width: 300px;
                height: 300px;
            }

            .shop-btn:hover {
                background: linear-gradient(45deg, #ff7700, #ff8c00) !important;
                transform: translateY(-8px) scale(1.1) !important;
                box-shadow: 0 20px 40px rgba(255, 140, 0, 0.5) !important;
            }

            .shop-btn svg {
                transition: transform 0.3s ease !important;
            }

            .shop-btn:hover svg {
                transform: rotate(10deg) scale(1.1) !important;
            }

            /* Modal Styles */
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                backdrop-filter: blur(10px);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }

            .modal-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            .modal-content {
                background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
                border-radius: 20px;
                padding: 40px;
                max-width: 500px;
                width: 90%;
                max-height: 90vh;
                position: relative;
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
                border: 1px solid rgba(255, 255, 255, 0.1);
                transform: scale(0.7) translateY(50px);
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }

            .modal-overlay.active .modal-content {
                transform: scale(1) translateY(0);
            }

            .modal-close {
                position: absolute;
                top: 15px;
                right: 20px;
                background: none;
                border: none;
                font-size: 28px;
                color: #999;
                cursor: pointer;
                transition: all 0.3s ease;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
            }

            .modal-close:hover {
                color: #ff4757;
                background: rgba(255, 71, 87, 0.1);
                transform: rotate(90deg);
            }

            .modal-title {
                text-align: center;
                font-size: 2rem;
                margin-bottom: 30px;
                color: #ffffff;
                background: linear-gradient(45deg, #ff4757, #ff6b7d);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .tab-container {
                margin-bottom: 30px;
            }

            .tab-buttons {
                display: flex;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 12px;
                padding: 4px;
                margin-bottom: 20px;
            }

            .tab-button {
                flex: 1;
                padding: 12px 20px;
                background: none;
                border: none;
                color: #999;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                border-radius: 8px;
                transition: all 0.3s ease;
                position: relative;
            }

            .tab-button.active {
                color: #ffffff;
                background: linear-gradient(45deg, #ff4757, #ff6b7d);
                box-shadow: 0 4px 15px rgba(255, 71, 87, 0.3);
            }

            .tab-button:not(.active):hover {
                color: #ffffff;
                background: rgba(255, 255, 255, 0.1);
            }

            .tab-content {
                display: none;
                animation: fadeIn 0.3s ease;
            }

            .tab-content.active {
                display: block;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .server-info {
                space-y: 20px;
            }

            .info-group {
                margin-bottom: 20px;
            }

            .info-label {
                display: block;
                font-size: 0.9rem;
                color: #999;
                margin-bottom: 8px;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .info-value {
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 8px;
                padding: 12px 16px;
                color: #ffffff;
                font-family: "Monaco", "Menlo", "Ubuntu Mono", monospace;
                font-size: 1rem;
                position: relative;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .info-value:hover {
                background: rgba(255, 255, 255, 0.1);
                border-color: rgba(255, 71, 87, 0.5);
                transform: translateY(-2px);
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }

            .copy-btn {
                background: none;
                border: none;
                color: #999;
                cursor: pointer;
                padding: 4px;
                border-radius: 4px;
                transition: all 0.3s ease;
                opacity: 0;
            }

            .info-value:hover .copy-btn {
                opacity: 1;
            }

            .copy-btn:hover {
                color: #ff4757;
                background: rgba(255, 71, 87, 0.1);
            }

            .version-note {
                background: rgba(255, 140, 0, 0.1);
                border: 1px solid rgba(255, 140, 0, 0.3);
                border-radius: 8px;
                padding: 12px;
                margin-top: 15px;
                color: #ffb347;
                font-size: 0.9rem;
                text-align: center;
            }

            /* Discord Section */
            .discord {
                padding: 100px 0;
                background: rgba(88, 101, 242, 0.03);
                text-align: center;
                position: relative;
            }

            .discord::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: radial-gradient(
                    circle at 50% 50%,
                    rgba(220, 53, 69, 0.1) 0%,
                    transparent 70%
                );
            }

            .discord-content {
                max-width: 600px;
                margin: 0 auto;
                position: relative;
                z-index: 2;
                transition: transform 0.3s ease;
            }

            .discord-content:hover {
                transform: translateY(-10px);
            }

            .discord h2 {
                font-size: 2.5rem;
                margin-bottom: 20px;
                color: #5865f2;
                transition: all 0.3s ease;
                cursor: default;
            }

            .discord-content:hover h2 {
                color: #4752c4;
                transform: scale(1.05);
            }

            .discord p {
                font-size: 1.2rem;
                color: #b8c5d6;
                margin-bottom: 40px;
                line-height: 1.6;
                transition: all 0.3s ease;
            }

            .discord-content:hover p {
                color: #ffffff;
            }

            .discord-btn {
                background: linear-gradient(45deg, #5865f2, #4752c4);
                color: white;
                padding: 18px 40px;
                border-radius: 12px;
                text-decoration: none;
                font-size: 1.1rem;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                box-shadow: 0 4px 15px rgba(88, 101, 242, 0.3);
                position: relative;
                overflow: hidden;
            }

            .discord-btn::before {
                content: "";
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                background: rgba(255, 255, 255, 0.1);
                transition: all 0.6s ease;
                border-radius: 50%;
                transform: translate(-50%, -50%);
            }

            .discord-btn:hover::before {
                width: 300px;
                height: 300px;
            }

            .discord-btn:hover {
                background: linear-gradient(45deg, #4752c4, #5865f2);
                transform: translateY(-8px) scale(1.1);
                box-shadow: 0 20px 40px rgba(88, 101, 242, 0.5);
            }

            .discord-btn svg {
                transition: transform 0.3s ease;
            }

            .discord-btn:hover svg {
                transform: rotate(10deg) scale(1.1);
            }

            /* Responsive */
            @media (max-width: 768px) {
                .hero-content {
                    grid-template-columns: 1fr;
                    gap: 40px;
                    text-align: center;
                }

                .hero-left {
                    order: 1;
                }

                .hero-right {
                    display: none;
                }

                .cta-buttons {
                    justify-content: center;
                }

                .features-grid {
                    grid-template-columns: 1fr;
                }

                .stats-grid {
                    grid-template-columns: 1fr;
                }
            }

            /* Floating particles */
            .particle {
                position: absolute;
                background: rgba(220, 53, 69, 0.1);
                border-radius: 50%;
                pointer-events: none;
                animation: float 8s ease-in-out infinite;
                transition: all 0.3s ease;
            }

            .particle:hover {
                background: rgba(220, 53, 69, 0.3);
                transform: scale(1.5);
            }

            @keyframes float {
                0%,
                100% {
                    transform: translateY(0px) rotate(0deg);
                    opacity: 0.7;
                }
                50% {
                    transform: translateY(-30px) rotate(180deg);
                    opacity: 1;
                }
            }

            /* Copy feedback */
            .copy-feedback {
                position: fixed;
                top: 20px;
                right: 20px;
                background: rgba(220, 53, 69, 0.9);
                color: white;
                padding: 10px 20px;
                border-radius: 8px;
                opacity: 0;
                transform: translateY(-20px);
                transition: all 0.3s ease;
                z-index: 10001;
            }

            .copy-feedback.show {
                opacity: 1;
                transform: translateY(0);
            }
            footer {
                z-index: 5;
                left: 0;
                width: 100%;
                background-color: #f9f9f9;
                padding: 20px;
                background: #e2e2e2;
                padding: 40px 20px 20px;
                color: #fff;
                bottom: 0;
            }

            .footer-section {
                display: flex;
                justify-content: space-around;
                flex-wrap: wrap;
                max-width: 1200px;
                margin: 0 auto;
            }
            .footer-column {
                margin: 0 20px 20px;
            }

            .footer-column h3 {
                color: black;
                margin-bottom: 15px;
            }

            .footer-column ul {
                list-style: none;
                padding: 0;
            }

            .footer-column ul li {
                margin-bottom: 8px;
            }

            .footer-column ul li a {
                color: #5b5b5b;
                text-decoration: none;
                transition: color 0.3s;
            }

            .footer-column ul li a:hover {
                color: black;
            }
            .footer-bottom {
                text-align: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid black;
            }
            .footer-bottom a {
                color: #5b5b5b;
                text-decoration: none;
                transition: color 0.3s;
            }

            .footer-bottom a:hover {
                color: black;
            }
            .mojang-notice {
                color: #5b5b5b;
                font-size: 0.9em;
                margin-top: 10px;
            }
            .footer-p {
                color: white;
            }
            footer {
                background-color: #090909;
                color: #fff;
            }
            .footer-column h3 {
                color: #fff;
            }
            .footer-column ul li a {
                color: #9e9e9e;
            }
            .footer-column ul li a:hover {
                color: #fff;
            }
            .footer-column .bi-discord {
                color: #7289da;
            }
            .footer-column .bi-discord:hover {
                color: #fff;
            }
            .footer-bottom {
                border-color: white;
            }
            .footer-bottom a {
                color: #9e9e9e;
            }
            .footer-bottom a:hover {
                color: #fff;
            }
            hr {
                color: white;
            }
        </style>
    </head>
    <body>
        <!-- Hero Section -->
        <section class="hero" id="home">
            <div class="container">
                <div class="hero-content">
                    <div class="hero-left">
                        <h1 class="hero-slogan">Your journey starts here</h1>
                        <p class="hero-text">
                            Join our community and create your own path. Discover, build, and survive in our unique world full of adventure and endless possibilities on one of the latest versions (1.21.4+).
                        </p>

                        <div class="server-ip" onclick="copyIP()">
                            <span>mc.sentrysmp.eu</span>
                        </div>

                        <div class="cta-buttons">
                            <a
                                href="#"
                                class="btn btn-primary"
                                onclick="openModal()"
                                >Start Playing</a
                            >
                            <a href="#features" class="btn btn-secondary"
                                >Learn More</a
                            >
                        </div>
                    </div>

                    <div class="hero-right">
                        <div class="hero-image">
                            <div class="hero-image-content">
                                <img
                                    src="images/sen.png"
                                    alt="SentrySMP Lobby"
                                    style="width: 25rem; height: 25rem"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="scroll-indicator"
                onclick="document.querySelector('#features').scrollIntoView({behavior: 'smooth'})"
            ></div>

            <!-- Floating particles -->
            <div
                class="particle"
                style="
                    top: 20%;
                    left: 10%;
                    width: 4px;
                    height: 4px;
                    animation-delay: 0s;
                "
            ></div>
            <div
                class="particle"
                style="
                    top: 60%;
                    left: 80%;
                    width: 6px;
                    height: 6px;
                    animation-delay: 2s;
                "
            ></div>
            <div
                class="particle"
                style="
                    top: 30%;
                    left: 70%;
                    width: 3px;
                    height: 3px;
                    animation-delay: 4s;
                "
            ></div>
            <div
                class="particle"
                style="
                    top: 80%;
                    left: 20%;
                    width: 5px;
                    height: 5px;
                    animation-delay: 1s;
                "
            ></div>
        </section>

        <!-- Why Play Section -->
        <section class="why-play" id="features">
            <div class="container">
                <h2
                    class="section-title"
                    data-aos="fade-up"
                    data-aos-duration="800"
                >
                    Why Play on SentrySMP?
                </h2>

                <div class="features-grid">
                    <div
                        class="feature-card"
                        data-aos="fade-up"
                        data-aos-delay="100"
                    >
                        <span class="feature-icon"
                            ><i class="bi bi-shield-fill"></i
                        ></span>
                        <h3>Security</h3>
                        <p>
                            Advanced anti-cheat systems and active moderation ensure a fair-play environment for all players.
                        </p>
                    </div>

                    <div
                        class="feature-card"
                        data-aos="fade-up"
                        data-aos-delay="200"
                    >
                        <span class="feature-icon"
                            ><i class="bi bi-controller"></i
                        ></span>
                        <h3>Unique Content</h3>
                        <p>
                            Custom plugins, events, and mini-games you won't find anywhere else. We constantly add new content. We also have a training server and other features to help you improve your game.
                        </p>
                    </div>

                    <div
                        class="feature-card"
                        data-aos="fade-up"
                        data-aos-delay="300"
                    >
                        <span class="feature-icon"
                            ><i class="bi bi-people-fill"></i
                        ></span>
                        <h3>Great Community</h3>
                        <p>
                            Friendly players, helpful staff, and an active Discord server full of fun. You can share ideas and contribute to improving the server. Voting is also valuable, you can influence the server.
                        </p>
                    </div>

                    <div
                        class="feature-card"
                        data-aos="fade-up"
                        data-aos-delay="400"
                    >
                        <span class="feature-icon"
                            ><i class="bi bi-lightning-charge-fill"></i
                        ></span>
                        <h3>High Performance</h3>
                        <p>
                            Powerful servers with minimal latency and nearly 100% uptime for the best gaming experience.
                        </p>
                    </div>

                    <div
                        class="feature-card"
                        data-aos="fade-up"
                        data-aos-delay="500"
                    >
                        <span class="feature-icon"
                            ><i class="bi bi-trophy-fill"></i
                        ></span>
                        <h3>Rewards for Almost Everything</h3>
                        <p>
                            You can get rewards for voting for the server, buying something on this page, daily logins, AFK...
                        </p>
                    </div>

                    <div
                        class="feature-card"
                        data-aos="fade-up"
                        data-aos-delay="600"
                    >
                        <span class="feature-icon"
                            ><i class="bi bi-arrow-clockwise"></i
                        ></span>
                        <h3>Regular Updates</h3>
                        <p>
                            The server is constantly improved and updated according to the latest trends and feedback. With major updates, the map resets and a new season begins.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats" id="stats">
            <div class="container">
                <h2
                    class="section-title"
                    data-aos="fade-up"
                    data-aos-duration="800"
                >
                    Server Stats
                </h2>

                <div class="stats-grid">
                    <div
                        class="stat-card"
                        data-aos="zoom-in"
                        data-aos-delay="100"
                    >
                        <span class="stat-number" data-target="13089">0</span>
                        <span class="stat-label">Registered Players</span>
                    </div>

                    <div
                        class="stat-card"
                        data-aos="zoom-in"
                        data-aos-delay="200"
                    >
                        <span
                            class="stat-number"
                            data-target="0"
                            id="current-players"
                            >0</span
                        >
                        <span class="stat-label">Currently Playing</span>
                    </div>

                    <div
                        class="stat-card"
                        data-aos="zoom-in"
                        data-aos-delay="400"
                    >
                        <span class="stat-number" data-target="99.7">0</span>
                        <span class="stat-label">Uptime %</span>
                    </div>

                    <div
                        class="stat-card"
                        data-aos="zoom-in"
                        data-aos-delay="300"
                    >
                        <span
                            class="stat-number"
                            data-target="0"
                            id="discord-members"
                            >0</span
                        >
                        <span class="stat-label">People on Discord</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Shop Section -->
        <section class="shop" id="shop">
            <div class="container">
                <div
                    class="shop-content scroll-element"
                    data-aos="fade-up"
                    data-aos-duration="800"
                >
                    <h2 data-aos="zoom-in" data-aos-delay="100">
                        <i class="bi bi-bag-fill"></i> One Big Store
                    </h2>
                    <p data-aos="fade-up" data-aos-delay="200">
                        This entire page is one big store! Buy everything you need for your game here, from special items to unique perks for your Minecraft experience.
                    </p>
                    <a
                        href="home"
                        class="shop-btn"
                        data-aos="zoom-in"
                        data-aos-delay="300"
                    >
                        <i class="bi bi-cart"></i>
                        Shop Now
                    </a>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="faq" id="faq">
            <div class="container">
                <h2
                    class="section-title"
                    data-aos="fade-up"
                    data-aos-duration="800"
                >
                    Frequently Asked Questions
                </h2>

                <div data-aos="fade-up" data-aos-delay="200">
                    <div
                        class="faq-item"
                        data-aos="fade-right"
                        data-aos-delay="100"
                    >
                        <div class="faq-question">
                            <span>How do I join the server?</span>
                            <span class="faq-toggle">▼</span>
                        </div>
                        <div class="faq-answer">
                            Just open Minecraft Java or Bedrock edition. For Java, add a new server with the IP "mc.sentrysmp.eu" and join. The server supports version 1.21.4 and above. For Bedrock, add a new server with the IP "pe.sentrysmp.eu" and port "25565", then join. The server supports the latest Bedrock version.
                        </div>
                    </div>

                    <div
                        class="faq-item"
                        data-aos="fade-right"
                        data-aos-delay="200"
                    >
                        <div class="faq-question">
                            <span>Is the server free?</span>
                            <span class="faq-toggle">▼</span>
                        </div>
                        <div class="faq-answer">
                            Yes, the server is completely free! However, we offer premium ranks with benefits, crate keys, shards for buying spawners, and a battle pass for the most active players. We give out keys through KeyAll events, so watch Discord. Purchased items cannot be refunded. These store rewards are paid in euros and are exclusively for people who want to support the server or get something extra.
                        </div>
                    </div>

                    <div
                        class="faq-item"
                        data-aos="fade-right"
                        data-aos-delay="300"
                    >
                        <div class="faq-question">
                            <span>Are mods allowed?</span>
                            <span class="faq-toggle">▼</span>
                        </div>
                        <div class="faq-answer">
                            We allow OptiFine and basic mods for performance improvement. Client-side mods are also fine as long as they don't disrupt the game for others. Cheats and unfair advantage mods are strictly prohibited.
                        </div>
                    </div>

                    <div
                        class="faq-item"
                        data-aos="fade-right"
                        data-aos-delay="400"
                    >
                        <div class="faq-question">
                            <span>What do we offer in the shop?</span>
                            <span class="faq-toggle">▼</span>
                        </div>
                        <div class="faq-answer">
                            In our store, we offer a wide range of items and resources to help you improve your game and gain new experiences. The most basic are keys, which help you open crates—random loot boxes with chances to win something. If you don't prefer randomness, you can buy shards, which you can exchange in-game for spawners with different mobs. Our spawners are specially customized to enhance gameplay and provide new experiences. Then there are ranks; each rank gives you improvements for the whole server, covering a wide range of things, valid for 30 days. We have multiple rank levels. The last is the battle pass, a special pass that helps you gain more experience and improve your game in battles.
                        </div>
                    </div>

                    <div
                        class="faq-item"
                        data-aos="fade-right"
                        data-aos-delay="500"
                    >
                        <div class="faq-question">
                            <span>What if I have a problem on the server?</span>
                            <span class="faq-toggle">▼</span>
                        </div>
                        <div class="faq-answer">
                            You can contact us using #ticket on the Discord server; we have many people who want to help solve your problems. If you have trouble receiving items after payment, create a ticket and tag our web developer (@Taneq). Everything is stored in databases so we have enough evidence if something goes wrong and can give you your paid items retroactively. We don't want to cheat anyone out of money. At the same time, no one gets anything for free—we want to be fair to each other.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Discord Section -->
        <!-- Discord Section -->
        <section class="discord" id="discord">
            <div class="container">
                <div class="discord-content scroll-element">
                    <h2><i class="bi bi-discord"></i> Join Discord</h2>
                    <p>
                        Chat with players, join competitions, follow server news, and be part of our great community!
                    </p>
                    <a
                        href="https://discord.gg/gXrXMwpuH4"
                        target="_blank"
                        class="discord-btn"
                    >
                        <i class="bi bi-discord"></i>
                        Join us on Discord
                    </a>
                </div>
            </div>
        </section>

        <!-- Modal -->
        <div class="modal-overlay" id="serverModal">
            <div class="modal-content">
                <button class="modal-close" onclick="closeModal()">
                    &times;
                </button>
                <h2 class="modal-title">Join the server</h2>

                <div class="tab-container">
                    <div class="tab-buttons">
                        <button
                            class="tab-button active"
                            onclick="switchTab('java')"
                        >
                            Java Edition
                        </button>
                        <button
                            class="tab-button"
                            onclick="switchTab('bedrock')"
                        >
                            Bedrock Edition
                        </button>
                    </div>

                    <div class="tab-content active" id="java-tab">
                        <div class="server-info">
                            <div class="info-group">
                                <label class="info-label">Server IP</label>
                                <div
                                    class="info-value"
                                    onclick="copyToClipboard('mc.sentrysmp.eu')"
                                >
                                    mc.sentrysmp.eu
                                    <button class="copy-btn" title="Copy">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="info-group">
                                <label class="info-label">Version</label>
                                <div class="info-value">1.21.4+</div>
                            </div>
                            <div class="version-note">
                                💡 We recommend version 1.21.4 for the best experience
                            </div>
                        </div>
                    </div>

                    <div class="tab-content" id="bedrock-tab">
                        <div class="server-info">
                            <div class="info-group">
                                <label class="info-label">Server IP</label>
                                <div
                                    class="info-value"
                                    onclick="copyToClipboard('pe.sentrysmp.eu')"
                                >
                                    pe.sentrysmp.eu
                                    <button class="copy-btn" title="Copy">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="info-group">
                                <label class="info-label">Port</label>
                                <div
                                    class="info-value"
                                    onclick="copyToClipboard('25565')"
                                >
                                    25565
                                    <button class="copy-btn" title="Copy">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="info-group">
                                <label class="info-label">Version</label>
                                <div class="info-value">1.21.x - newest</div>
                            </div>
                            <div class="version-note">
                                💡 We support all bedrock platforms (mobile,
                                Xbox, PlayStation, Nintendo Switch, ...)
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Discord statistics (interval 30s) - only to data-target
            function updateDiscordStats() {
                fetch("discord.php")
                    .then((res) => res.json())
                    .then((data) => {
                        const statsEl =
                            document.getElementById("discord-members");
                        if (data.total !== undefined && statsEl) {
                            statsEl.setAttribute("data-target", data.total);
                        }
                    });
            }

            // Server statistics (interval 30s) - only to data-target
            function updateServerStats() {
                fetch("player_count.php")
                    .then((response) => response.json())
                    .then((data) => {
                        const statsEl =
                            document.getElementById("current-players");
                        if (data.status === "success" && statsEl) {
                            statsEl.setAttribute("data-target", data.players);
                        }
                    });
            }

            // První načtení
            updateDiscordStats();
            updateServerStats();

            // Intervalové načítání každých 30 sekund
            setInterval(updateDiscordStats, 30000);
            setInterval(updateServerStats, 30000);
        </script>
        <footer>
            <div class="footer-section">
                <div class="footer-column">
                    <h3>Support</h3>
                    <ul>
                        <li><a href="support.html">Support</a></li>
                        <li>
                            <a
                                href="https://discord.gg/gXrXMwpuH4"
                                target="_blank"
                                >Report Issue</a
                            >
                        </li>
                        <li><a href="vote.html">Vote For Us</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>About Us</h3>
                    <ul>
                        <li><a href="about.html">About server</a></li>
                        <li><a href="our-team.php">Our Team</a></li>
                        <li><a href="news.php">News</a></li>
                        <li><a href="changelog.html">Web Changelog</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Rules</h3>
                    <ul>
                        <li><a href="rules.html">Discord Server Rules</a></li>
                        <li>
                            <a href="rules-minecraft.html"
                                >Minecraft Server Rules</a
                            >
                        </li>
                        <li>
                            <a href="privacy-policy.html">Privacy Policy</a>
                        </li>
                        <li><a href="terms-of-use.html">Terms of Use</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="footer-p">© 2025 Sentry SMP. All rights reserved.</p>
                <p class="mojang-notice">
                    We are not affiliated with or endorsed by Mojang, AB.
                </p>
            </div>
        </footer>

        <!-- AOS JS a inicializace -->
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <script>
            AOS.init({
                once: true,
                duration: 1000,
            });

            // Univerzální funkce pro animovaný copy-feedback
            function showCopyFeedback(text) {
                const feedback = document.createElement("div");
                feedback.className = "copy-feedback";
                feedback.textContent = text;
                document.body.appendChild(feedback);

                // Animace IN
                setTimeout(() => {
                    feedback.classList.add("show");
                }, 10);

                // Animace OUT
                setTimeout(() => {
                    feedback.classList.remove("show");
                    setTimeout(() => {
                        feedback.remove();
                    }, 300);
                }, 2000);
            }

            // Funkce pro kopírování IP adresy v hero sekci
            function copyIP() {
                const ip = "mc.sentrysmp.eu";
                navigator.clipboard.writeText(ip).then(() => {
                    showCopyFeedback("Copied!");
                });
            }

            // Responzivní animace čísel ve statistikách
            function animateNumbers() {
                const statNumbers = document.querySelectorAll(
                    ".stat-number[data-target]",
                );

                const observer = new IntersectionObserver(
                    (entries) => {
                        entries.forEach((entry) => {
                            if (
                                entry.isIntersecting &&
                                !entry.target.hasAttribute("data-animated")
                            ) {
                                const stat = entry.target;
                                const targetValue =
                                    stat.getAttribute("data-target");
                                const target = parseFloat(targetValue);
                                const isDecimal = targetValue.includes(".");
                                let current = 0;

                                // Responzivní rychlost animace podle velikosti čísla
                                const duration = Math.min(
                                    Math.max(target / 10, 1000),
                                    3000,
                                );
                                const steps = 60;
                                const increment = target / steps;
                                const stepTime = duration / steps;

                                stat.setAttribute("data-animated", "true");

                                const timer = setInterval(() => {
                                    current += increment;
                                    if (current >= target) {
                                        current = target;
                                        clearInterval(timer);
                                    }

                                    if (isDecimal) {
                                        stat.textContent = current.toFixed(1);
                                    } else {
                                        stat.textContent =
                                            Math.floor(current).toLocaleString(
                                                "cs-CZ",
                                            );
                                    }
                                }, stepTime);
                            }
                        });
                    },
                    {
                        threshold: 0.5,
                        rootMargin: "0px 0px -100px 0px",
                    },
                );

                statNumbers.forEach((stat) => {
                    observer.observe(stat);
                });
            }

            // Načtení počtu hráčů a spuštění animace
            function loadPlayerCountAndAnimate() {
                fetch("player_count.php")
                    .then((response) => response.json())
                    .then((data) => {
                        const playerCountElement =
                            document.getElementById("current-players");
                        if (data.status === "success" && playerCountElement) {
                            playerCountElement.setAttribute(
                                "data-target",
                                data.players,
                            );
                            animateNumbers();
                        }
                    })
                    .catch((error) => {
                        console.error("Chyba při načítání počtu hráčů:", error);
                        // Fallback na výchozí hodnotu
                        const playerCountElement =
                            document.getElementById("current-players");
                        if (playerCountElement) {
                            playerCountElement.setAttribute(
                                "data-target",
                                "94",
                            );
                            animateNumbers();
                        }
                    });

                // Načtení počtu lidí na Discordu a animace
                fetch("discord.php")
                    .then((response) => response.json())
                    .then((data) => {
                        const discordMembersElement =
                            document.getElementById("discord-members");
                        if (
                            data &&
                            typeof data.members === "number" &&
                            discordMembersElement
                        ) {
                            discordMembersElement.setAttribute(
                                "data-target",
                                data.members,
                            );
                            animateNumbers();
                        }
                    })
                    .catch((error) => {
                        console.error(
                            "Chyba při načítání počtu lidí na Discordu:",
                            error,
                        );
                        // Fallback na výchozí hodnotu
                        const discordMembersElement =
                            document.getElementById("discord-members");
                        if (discordMembersElement) {
                            discordMembersElement.setAttribute(
                                "data-target",
                                "1200",
                            );
                            animateNumbers();
                        }
                    });
            }

            // Spuštění animace při načtení stránky
            window.addEventListener("load", () => {
                loadPlayerCountAndAnimate();
            });

            // FAQ funkcionalita - defaultně zavřené, na klik se otevře/zavře
            document.addEventListener("DOMContentLoaded", function () {
                const faqItems = document.querySelectorAll(".faq-item");

                faqItems.forEach((item) => {
                    const question = item.querySelector(".faq-question");

                    question.addEventListener("click", () => {
                        const isActive = item.classList.contains("active");

                        // Toggle aktuální položku
                        if (isActive) {
                            item.classList.remove("active");
                        } else {
                            // Zavřít všechny ostatní FAQ položky
                            faqItems.forEach((otherItem) => {
                                if (otherItem !== item) {
                                    otherItem.classList.remove("active");
                                }
                            });
                            // Otevřít aktuální položku
                            item.classList.add("active");
                        }
                    });

                    // Přidat cursor pointer na otázky
                    question.style.cursor = "pointer";
                });
            });

            // Plynulý scroll pro anchor odkazy
            document.addEventListener("DOMContentLoaded", function () {
                const anchorLinks = document.querySelectorAll('a[href^="#"]');

                anchorLinks.forEach((link) => {
                    link.addEventListener("click", function (e) {
                        e.preventDefault();

                        const targetId = this.getAttribute("href");
                        const targetElement = document.querySelector(targetId);

                        if (targetElement) {
                            targetElement.scrollIntoView({
                                behavior: "smooth",
                                block: "start",
                            });
                        }
                    });
                });
            });

            // Modal Functions
            function openModal() {
                const modal = document.getElementById("serverModal");
                modal.classList.add("active");
                document.body.style.overflow = "hidden";
            }

            function closeModal() {
                const modal = document.getElementById("serverModal");
                modal.classList.remove("active");
                document.body.style.overflow = "auto";
            }

            function switchTab(tabName) {
                // Remove active class from all tabs and buttons
                document
                    .querySelectorAll(".tab-button")
                    .forEach((btn) => btn.classList.remove("active"));
                document
                    .querySelectorAll(".tab-content")
                    .forEach((content) => content.classList.remove("active"));

                // Add active class to clicked button and corresponding content
                event.target.classList.add("active");
                document
                    .getElementById(tabName + "-tab")
                    .classList.add("active");
            }

            function copyToClipboard(text) {
                navigator.clipboard
                    .writeText(text)
                    .then(() => {
                        if (text === "sentrysmp.eu") {
                            showCopyFeedback("IP Copied!");
                        } else if (text === "19132") {
                            showCopyFeedback("Port Copied!");
                        } else {
                            showCopyFeedback("Copied!");
                        }
                    })
                    .catch(() => {
                        // Fallback pro starší prohlížeče
                        const textArea = document.createElement("textarea");
                        textArea.value = text;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand("copy");
                        document.body.removeChild(textArea);

                        showCopyFeedback("Copied!");
                    });
            }

            // Close modal when clicking outside
            document
                .getElementById("serverModal")
                .addEventListener("click", function (e) {
                    if (e.target === this) {
                        closeModal();
                    }
                });

            // Close modal with Escape key
            document.addEventListener("keydown", function (e) {
                if (e.key === "Escape") {
                    closeModal();
                }
            });
        </script>
    </body>
</html>
