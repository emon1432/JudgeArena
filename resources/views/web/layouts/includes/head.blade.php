<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>JudgeArena</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <link rel="stylesheet" href="{{ asset('web') }}/css/style.css">
    <script>
        (function() {
            try {
                var s = localStorage.getItem("judgearena-theme");
                var t = s === "dark" || s === "light" ? s : "light";
                document.documentElement.setAttribute("data-theme", t);
                document.documentElement.setAttribute("data-bs-theme", t);
            } catch (e) {}
        })();
    </script>
</head>
