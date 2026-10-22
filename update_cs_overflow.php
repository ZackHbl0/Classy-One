<?php
$file = 'C:/laragon/www/Classy-One-Mobile/lib/screens/courses_screen.dart';
$content = file_get_contents($file);

$oldCode = '  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24.0),
              decoration: BoxDecoration(
                color: const Color(0xFF708C70).withOpacity(0.08),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.class_outlined,
                size: 80,
                color: Color(0xFF708C70),
              ),
            ),
            const SizedBox(height: 24),
            const Text(
              \'Aucun cours disponible\',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2A322A),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              \'Votre professeur n\\\'a pas encore publié de cours pour votre classe.\',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Colors.grey[500],
                fontSize: 14,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: _fetchCourses,
              icon: const Icon(Icons.refresh),
              label: const Text(\'Actualiser\'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF708C70),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 12,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.redAccent),
            const SizedBox(height: 20),
            Text(
              _errorMessage ?? \'Une erreur est survenue\',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 16,
                color: Color(0xFF475569),
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _fetchCourses,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF708C70),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: const Text(\'Réessayer\'),
            ),
          ],
        ),
      ),
    );
  }';

$newCode = '  Widget _buildEmptyState() {
    final isSearching = _searchQuery.trim().isNotEmpty;

    return Center(
      child: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 16.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20.0),
              decoration: BoxDecoration(
                color: const Color(0xFF708C70).withOpacity(0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(
                isSearching ? Icons.search_off_rounded : Icons.class_outlined,
                size: 64,
                color: const Color(0xFF708C70),
              ),
            ),
            const SizedBox(height: 18),
            Text(
              isSearching ? \'Aucun résultat\' : \'Aucun cours disponible\',
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2A322A),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              isSearching
                  ? \'Aucun cours ne correspond à "\${_searchQuery.trim()}".\'
                  : \'Votre professeur n\\\'a pas encore publié de cours pour votre classe.\',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Colors.grey[500],
                fontSize: 13,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: isSearching
                  ? () {
                      _searchController.clear();
                      _onSearchChanged(\'\');
                    }
                  : _fetchCourses,
              icon: Icon(isSearching ? Icons.clear : Icons.refresh, size: 18),
              label: Text(isSearching ? \'Effacer la recherche\' : \'Actualiser\'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF708C70),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 10,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 16.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 56, color: Colors.redAccent),
            const SizedBox(height: 16),
            Text(
              _errorMessage ?? \'Une erreur est survenue\',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 15,
                color: Color(0xFF475569),
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: _fetchCourses,
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF708C70),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: const Text(\'Réessayer\'),
            ),
          ],
        ),
      ),
    );
  }';

if (strpos($content, $oldCode) !== false) {
    $content = str_replace($oldCode, $newCode, $content);
    file_put_contents($file, $content);
    echo "COURSES_SCREEN_UPDATED";
} else {
    echo "PATTERN_NOT_FOUND";
}
