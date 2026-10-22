<?php
$file = 'c:/laragon/www/Classy-One-Mobile/lib/screens/parent_dashboard_screen.dart';
$content = file_get_contents($file);

if ($content === false) {
    die("Cannot read file\n");
}

$oldHeaderRegex = '/Widget _buildTopHeader\(ParentInfo\? parent, ChildData activeChild, bool isDark\) \{.*?(?=\/\/ ── Extract all dynamic notifications)/s';

$newHeader = 'Widget _buildTopHeader(ParentInfo? parent, ChildData activeChild, bool isDark) {
    final parentFirstName = (parent?.name.isNotEmpty == true)
        ? parent!.name.trim().split(\' \').first
        : \'Mohamed\';

    final notifications = _getNotifications(activeChild);
    final unreadCount = notifications.where((n) => !_readNotificationIds.contains(n.id)).length;

    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        // ── Greeting Title matching img2 ──
        Text(
          \'Bonjour, \' + parentFirstName + \' !\',
          style: TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.w800,
            color: isDark ? Colors.white : const Color(0xFF0F172A),
            letterSpacing: -0.4,
          ),
        ),

        // ── Action Buttons matching img2 (Frosted Bell & Frosted Logout) ──
        Row(
          children: [
            // Notification Bell Button
            Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: () => _openNotificationCenter(notifications, activeChild.details.nomComplet),
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF1E2B22) : const Color(0xFFEFF8F3),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isDark ? Colors.white.withOpacity(0.08) : const Color(0xFFE2EDE6),
                      width: 1.0,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(isDark ? 0.2 : 0.03),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Stack(
                    clipBehavior: Clip.none,
                    children: [
                      Center(
                        child: Icon(
                          Icons.notifications_none_rounded,
                          size: 22,
                          color: isDark ? Colors.white : const Color(0xFF2D5A43),
                        ),
                      ),
                      if (unreadCount > 0)
                        Positioned(
                          top: 10,
                          right: 10,
                          child: Container(
                            width: 7,
                            height: 7,
                            decoration: const BoxDecoration(
                              color: Color(0xFFEF4444),
                              shape: BoxShape.circle,
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(width: 10),

            // Logout Button
            Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: _logout,
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF1E2B22) : const Color(0xFFEFF8F3),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isDark ? Colors.white.withOpacity(0.08) : const Color(0xFFE2EDE6),
                      width: 1.0,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(isDark ? 0.2 : 0.03),
                        blurRadius: 8,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Center(
                    child: Icon(
                      Icons.logout_rounded,
                      size: 21,
                      color: isDark ? Colors.white : const Color(0xFF1E293B),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  ';

$oldSelectorRegex = '/Widget _buildChildSelectorBar\(List<ChildData> children, bool isDark\) \{.*?(?=Widget _buildHeroCard)/s';

$newSelector = 'Widget _buildChildSelectorBar(List<ChildData> children, bool isDark) {
    if (children.length <= 1) {
      return const SizedBox.shrink();
    }

    return SizedBox(
      height: 48,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        physics: const BouncingScrollPhysics(),
        itemCount: children.length,
        separatorBuilder: (_, __) => const SizedBox(width: 22),
        itemBuilder: (context, index) {
          final child = children[index];
          final isSelected = index == _selectedChildIndex;

          final avatarAsset = (index % 2 == 0)
              ? \'assets/images/child_avatar_boy1.jpg\'
              : \'assets/images/child_avatar_boy2.jpg\';

          return GestureDetector(
            behavior: HitTestBehavior.opaque,
            onTap: () {
              if (_selectedChildIndex != index) {
                setState(() => _selectedChildIndex = index);
                _fetchDocuments(children[index].details.idStudent);
              }
            },
            child: Row(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // ── 3D Avatar with Active Emerald Halo/Border matching img2 ──
                Container(
                  width: 44,
                  height: 44,
                  padding: const EdgeInsets.all(2.5),
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: isSelected ? const Color(0xFF00A86B) : Colors.transparent,
                      width: 2.2,
                    ),
                  ),
                  child: ClipOval(
                    child: Image.asset(
                      avatarAsset,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => CircleAvatar(
                        backgroundColor: primaryGreen.withOpacity(0.15),
                        child: Text(
                          child.details.prenom.isNotEmpty ? child.details.prenom[0].toUpperCase() : \'E\',
                          style: TextStyle(
                            color: primaryGreen,
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),

                // ── Name & Class Label matching img2 ──
                Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      child.details.prenom + \' \' + child.details.nom,
                      style: TextStyle(
                        fontWeight: FontWeight.w700,
                        fontSize: 14.5,
                        color: isDark ? Colors.white : const Color(0xFF0F172A),
                        letterSpacing: -0.2,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      child.details.classe,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: isDark ? Colors.grey.shade400 : const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  ';

$newContent = preg_replace($oldHeaderRegex, $newHeader, $content, 1, $countHeader);
echo "Header replaced: $countHeader\n";

$newContent = preg_replace($oldSelectorRegex, $newSelector, $newContent, 1, $countSelector);
echo "Selector replaced: $countSelector\n";

if ($countHeader === 1 && $countSelector === 1) {
    file_put_contents($file, $newContent);
    echo "SUCCESS: Saved patched parent_dashboard_screen.dart\n";
} else {
    echo "ERROR: Could not replace both sections\n";
}
