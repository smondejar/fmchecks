    <?php if (Auth::check()): ?>
        </main>
        <footer class="footer">
            <p>&copy; <?= date('Y') ?> FM Checks - Facilities Management System</p>
        </footer>
    </div>
    <?php else: ?>
        </div>
    </div>
    <?php endif; ?>

    <script src="/js/app.js"></script>
</body>
</html>
