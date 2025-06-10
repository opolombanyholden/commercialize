<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès protégé - {{ $pdf['filename'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-8">
            <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-lock text-red-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Document Protégé</h1>
            <p class="text-gray-600">Ce document nécessite un mot de passe pour être consulté</p>
        </div>

        <!-- Informations du fichier -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Fichier:</span>
                <span class="text-sm text-gray-900 font-mono">{{ $pdf['filename'] }}</span>
            </div>
            
            @if($pdf['metadata']['title'] ?? false)
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Titre:</span>
                <span class="text-sm text-gray-900">{{ $pdf['metadata']['title'] }}</span>
            </div>
            @endif

            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Téléchargements restants:</span>
                <span class="text-sm text-gray-900 font-semibold">{{ $pdf['downloads_remaining'] }}</span>
            </div>
            
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Expire le:</span>
                <span class="text-sm text-gray-900" id="expiry-date">
                    {{ \Carbon\Carbon::parse($pdf['expires_at'])->format('d/m/Y à H:i') }}
                </span>
            </div>
        </div>

        <!-- Messages d'erreur -->
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <i class="fas fa-exclamation-triangle text-red-400 mt-1 mr-3"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <p class="text-red-700 text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Vérification de disponibilité -->
        @if(!$pdf['can_download'])
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <i class="fas fa-info-circle text-yellow-400 mt-1 mr-3"></i>
                <p class="text-yellow-700 text-sm">
                    Ce document n'est plus disponible au téléchargement.
                </p>
            </div>
        </div>
        @else
        <!-- Formulaire de mot de passe -->
        <form method="POST" action="{{ route('pdf.download', $pdf['token']) }}" id="password-form">
            @csrf
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Mot de passe
                </label>
                <div class="relative">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-12"
                        placeholder="Saisissez le mot de passe"
                        autocomplete="off">
                    <button 
                        type="button" 
                        id="toggle-password"
                        class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            <button 
                type="submit" 
                id="submit-btn"
                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center font-medium">
                <i class="fas fa-download mr-2"></i>
                <span id="btn-text">Télécharger le document</span>
                <i class="fas fa-spinner fa-spin ml-2 hidden" id="loading-icon"></i>
            </button>
        </form>

        <!-- Prévisualisation -->
        <div class="mt-4">
            <button 
                type="button" 
                id="preview-btn"
                class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors text-sm">
                <i class="fas fa-eye mr-2"></i>
                Prévisualiser les informations
            </button>
        </div>
        @endif

        <!-- Compteur de temps restant -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500">
                Temps restant: <span id="countdown" class="font-mono font-semibold"></span>
            </p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                eyeIcon.className = 'fas fa-eye';
            }
        });

        // Loading state on form submit
        document.getElementById('password-form').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');
            const loadingIcon = document.getElementById('loading-icon');
            
            submitBtn.disabled = true;
            btnText.textContent = 'Téléchargement...';
            loadingIcon.classList.remove('hidden');
        });

        // Countdown timer
        const expiryDate = new Date('{{ $pdf['expires_at'] }}');
        const countdownElement = document.getElementById('countdown');
        
        function updateCountdown() {
            const now = new Date();
            const diff = expiryDate - now;
            
            if (diff <= 0) {
                countdownElement.textContent = 'Expiré';
                countdownElement.className += ' text-red-600';
                return;
            }
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            countdownElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Preview functionality
        document.getElementById('preview-btn')?.addEventListener('click', async function() {
            const password = document.getElementById('password').value;
            if (!password) {
                alert('Veuillez saisir le mot de passe');
                return;
            }
            
            try {
                const response = await fetch(`{{ route('pdf.preview', $pdf['token']) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ password })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    alert(`Informations du document:\n- Taille: ${(data.size / 1024).toFixed(1)} KB\n- Téléchargements restants: ${data.downloads_remaining}`);
                } else {
                    alert('Mot de passe incorrect');
                }
            } catch (error) {
                alert('Erreur lors de la prévisualisation');
            }
        });
    </script>
</body>
</html>