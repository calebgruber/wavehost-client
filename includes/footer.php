</main>
    
    <!-- Footer -->
    <?php if (strpos($_SERVER['REQUEST_URI'], '/auth/') === false) : ?>
    <footer class="footer bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h5>WaveHost</h5>
                    <ul class="list-unstyled">
                        <li><a href="/about" class="text-white-50">About Us</a></li>
                        <li><a href="/about/careers" class="text-white-50">Careers</a></li>
                        <li><a href="/blog" class="text-white-50">Blog</a></li>
                        <li><a href="/support" class="text-white-50">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h5>Services</h5>
                    <ul class="list-unstyled">
                        <li><a href="/games" class="text-white-50">Game Servers</a></li>
                        <li><a href="/web" class="text-white-50">Web Hosting</a></li>
                        <li><a href="/vps" class="text-white-50">VPS Hosting</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h5>Support</h5>
                    <ul class="list-unstyled">
                        <li><a href="/dash/tickets" class="text-white-50">Support Tickets</a></li>
                        <li><a href="/dash/ticket/new" class="text-white-50">Open a Ticket</a></li>
                        <li><a href="/knowledge-base" class="text-white-50">Knowledge Base</a></li>
                        <li><a href="/server-status" class="text-white-50">Server Status</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="/legal/tos" class="text-white-50">Terms of Service</a></li>
                        <li><a href="/privacy" class="text-white-50">Privacy Policy</a></li>
                        <li><a href="/fair-use" class="text-white-50">Fair Use Policy</a></li>
                        <li><a href="/sla" class="text-white-50">Service Level Agreement</a></li>
                        <li><a href="/abuse" class="text-white-50">Report Abuse</a></li>
                    </ul>
                </div>
            </div>
            
            <hr class="mt-4 mb-4 bg-secondary">
            
            <div class="row">
                <div class="col-md-6">
                    <p class="text-white-50">&copy; <?php echo date('Y'); ?> WaveHost. All rights reserved.</p>
                </div>
                
                <div class="col-md-6 text-md-end">
                    <ul class="list-inline mb-0">
                        <li class="list-inline-item">
                            <a href="https://twitter.com/wavehost" class="text-white-50">
                                <i class="fab fa-twitter"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="https://facebook.com/wavehost" class="text-white-50">
                                <i class="fab fa-facebook"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="https://instagram.com/wavehost" class="text-white-50">
                                <i class="fab fa-instagram"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="https://github.com/wavehost" class="text-white-50">
                                <i class="fab fa-github"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="https://discord.gg/wavehost" class="text-white-50">
                                <i class="fab fa-discord"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom scripts -->
    <script src="/assets/js/main.js"></script>
    
    <?php if (isset($extraJS)) { echo $extraJS; } ?>
</body>
</html>