<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BFP Calamba</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">

    <div class="min-h-screen flex">
        
        <div class="hidden lg:flex w-1/2 bg-red-900 relative items-center justify-center overflow-hidden">
            <img src="https://images.unsplash.com/photo-1599235940068-132d729a43a0?q=80&w=2670&auto=format&fit=crop" 
                 alt="Firefighter Background" 
                 class="absolute inset-0 w-full h-full object-cover opacity-40">
            <div class="absolute inset-0 bg-gradient-to-br from-red-900/90 to-black/50"></div>

            <div class="relative z-10 text-center px-12">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/Bureau_of_Fire_Protection.png/1200px-Bureau_of_Fire_Protection.png" 
                     alt="BFP Logo" 
                     class="h-32 w-32 mx-auto mb-8 drop-shadow-2xl">
                <h1 class="text-4xl font-bold text-white mb-4">Fire Safety Intelligence Platform</h1>
                <p class="text-red-100 text-lg max-w-md mx-auto leading-relaxed">
                    Bureau of Fire Protection • City of Calamba
                    <br><span class="text-sm opacity-75">Ensuring public safety through data-driven fire prevention and response.</span>
                </p>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md space-y-8">
                
                <div class="lg:hidden text-center mb-8">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/Bureau_of_Fire_Protection.png/1200px-Bureau_of_Fire_Protection.png" 
                         alt="BFP Logo" class="h-16 w-16 mx-auto mb-4">
                    <h2 class="text-xl font-bold text-gray-900">BFP Calamba</h2>
                </div>

                <div class="text-left">
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Welcome back</h2>
                    <p class="mt-2 text-sm text-gray-500">Please enter your credentials to access the dashboard.</p>
                </div>

               <form class="mt-8 space-y-6" action="/login" method="POST">
    
    @csrf

    @if($errors->any())
        <div class="bg-red-50 text-red-500 text-sm p-3 rounded-lg text-center">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="space-y-5">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-regular fa-envelope text-gray-400"></i>
                </div>
                <input id="email" name="email" type="email" autocomplete="email" required 
                        value="{{ old('email') }}"
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500 sm:text-sm" 
                        placeholder="officer_bfp@gmail.com">
            </div>
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-lock text-gray-400"></i>
                </div>
                <input id="password" name="password" type="password" autocomplete="current-password" required 
                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500 sm:text-sm" 
                        placeholder="••••••••">
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <div class="flex items-center">
            <input id="remember-me" name="remember-me" type="checkbox" 
                   class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded cursor-pointer">
            <label for="remember-me" class="ml-2 block text-sm text-gray-900 cursor-pointer">
                Remember me
            </label>
        </div>

        <div class="text-sm">
            <a href="#" class="font-medium text-red-600 hover:text-red-500">
                Forgot your password?
            </a>
        </div>
    </div>

    <div>
        <button type="submit" 
                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors shadow-md hover:shadow-lg">
            Sign in
        </button>
    </div>
</form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-400">
                        &copy; 2024 Bureau of Fire Protection - Calamba City. <br>All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>