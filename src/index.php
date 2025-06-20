<?php
error_reporting(E_ALL);
$featuresDir = "features";
$pagesDir = "pages";
$templatesDir = "templates";
$toolsDir = "tools";

function read($dir)
{
    $ignorePaths = array(".", "..");
    $ignoredExtensions = array("css", "js", "json", "log", "md", "sql", "template", "txt", "xml");

    $entries = [];

    $handle = opendir($dir);
    while ($file = readdir($handle)) {
        if (in_array($file, $ignorePaths)) {
            continue;
        }

        $filePath = $dir . "/" . $file;

        if (is_dir($filePath)) {
            $entries[] = [
                'type' => 'dir',
                'path' => $filePath,
                'label' => preg_replace('/(?<!^)([A-Z])/', ' \\1', $file)
            ];
        } elseif (is_file($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (in_array($ext, $ignoredExtensions)) {
                continue;
            }
            $entries[] = [
                'type' => 'file',
                'path' => $filePath,
                'label' => $file
            ];
        }
    }

    closedir($handle);
    usort($entries, function ($a, $b) {
        return strcasecmp($a['label'], $b['label']);
    });

    foreach ($entries as $entry) {
        echo "<li class='list-group-item'><a href='" . htmlspecialchars($entry['path']) . "'>" .
            htmlspecialchars($entry['label']) . "</a></li>\r\n";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>GStraccini-bot | Playground</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <style>
        :root {
            --primary-green: #0dd682;
            --dark-green: #0bc26e;
            --light-green: #e8f8f2;
            --text-dark: #2c3e50;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .hero-section {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 3rem;
            box-shadow: var(--shadow);
        }

        .hero-section h1 {
            font-weight: 700;
            font-size: 3rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin: 0;
        }

        .hero-logo {
            max-width: 200px;
            height: auto;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }

        .section-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            overflow: hidden;
            border: none;
        }

        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .card-header {
            padding: 1.5rem;
            font-weight: 600;
            font-size: 1.25rem;
            border-bottom: 3px solid;
        }

        .features-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-bottom-color: #28a745;
        }

        .pages-header {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
            border-bottom-color: #ffc107;
        }

        .templates-header {
            background: linear-gradient(135deg, #17a2b8, #6f42c1);
            color: white;
            border-bottom-color: #17a2b8;
        }

        .tools-header {
            background: linear-gradient(135deg, #dc3545, #e83e8c);
            color: white;
            border-bottom-color: #dc3545;
        }

        .list-group {
            border: none;
        }

        .list-group-item {
            border: none;
            border-bottom: 1px solid #f8f9fa;
            padding: 0;
            background: transparent;
        }

        .list-group-item:last-child {
            border-bottom: none;
        }

        .list-group-item a {
            display: block;
            padding: 1.25rem 2rem;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .list-group-item a:hover {
            background: var(--light-green);
            color: var(--dark-green);
            padding-left: 2.5rem;
        }

        .list-group-item a:before {
            content: '\f054';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            position: absolute;
            left: 1.5rem;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .list-group-item a:hover:before {
            opacity: 1;
            left: 2rem;
        }

        .footer {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--dark-green) 100%);
            color: white;
            padding: 2rem 0;
            margin-top: 4rem;
            box-shadow: 0 -4px 6px rgba(0, 0, 0, 0.1);
        }

        .footer a {
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer a:hover {
            color: #f8f9fa;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .developer-photo {
            height: 32px;
            width: auto;
            border-radius: 4px;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
            vertical-align: middle;
        }

        .developer-photo:hover {
            transform: scale(1.1);
        }

        .github-icon {
            width: 24px;
            height: 24px;
            vertical-align: middle;
            margin-right: 0.5rem;
            transition: transform 0.3s ease;
        }

        .github-icon:hover {
            transform: rotate(360deg);
        }

        .main-content {
            animation: fadeInUp 0.8s ease-out;
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

        .section-icon {
            font-size: 1.5rem;
            margin-right: 0.5rem;
        }

        @media (max-width: 768px) {
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .hero-logo {
                max-width: 150px;
                margin-bottom: 1rem;
            }
            
            .section-card {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center text-center">
                <div class="col-md-3 mb-3 mb-md-0">
                    <img src="https://bot.straccini.com/images/logo-white.png" 
                         alt="GStraccini-bot Logo" 
                         class="hero-logo img-fluid" />
                </div>
                <div class="col-md-9">
                    <h1 class="display-4">
                        <i class="fas fa-robot me-3"></i>
                        Playground
                    </h1>
                    <p class="lead mb-0">Explore features, pages, templates, and tools</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container-fluid px-4 main-content">
        <div class="row g-4 justify-content-center">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card section-card h-100">
                    <div class="card-header features-header text-center">
                        <i class="fas fa-star section-icon"></i>
                        Features
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php read($featuresDir) ?>
                    </ul>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card section-card h-100">
                    <div class="card-header pages-header text-center">
                        <i class="fas fa-file-alt section-icon"></i>
                        Pages
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php read($pagesDir) ?>
                    </ul>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card section-card h-100">
                    <div class="card-header templates-header text-center">
                        <i class="fas fa-layer-group section-icon"></i>
                        Templates
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php read($templatesDir) ?>
                    </ul>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card section-card h-100">
                    <div class="card-header tools-header text-center">
                        <i class="fas fa-tools section-icon"></i>
                        Tools
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php read($toolsDir) ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-2">
                        <i class="fas fa-code me-2"></i>
                        Developed by 
                        <a href="https://guilherme.stracini.com.br/?utm_campaign=project&utm_media=bot-playground&utm_source=straccini.com"
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="fw-bold">
                            <img alt="Guilherme Branco Stracini"
                                 class="developer-photo me-2"
                                 loading="lazy"
                                 src="https://guilherme.stracini.com.br/photo.png">
                            Guilherme Branco Stracini
                        </a>
                    </p>
                    <p class="mb-0">
                        <i class="fab fa-github me-2"></i>
                        Repository: 
                        <a href="https://github.com/guibranco/gstraccini-bot-playground"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="fw-bold">
                            <svg class="github-icon" fill="currentColor" role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <title>GitHub</title>
                                <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"></path>
                            </svg>
                            GitHub
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.section-card').forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        });
    </script>
</body>

</html>
