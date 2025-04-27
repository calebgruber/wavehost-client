<?php
// legal/service-level-agreement.php - Service Level Agreement Page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Set page title
$pageTitle = 'Service Level Agreement';
?>
<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <h1 class="fw-bold mb-4">Service Level Agreement (SLA)</h1>
            
            <div class="card bg-dark mb-4">
                <div class="card-body">
                    <p class="mb-4">This Service Level Agreement ("SLA") is part of the agreement between WaveHost ("Provider") and the customer ("Customer") for the provision of hosting services. This SLA outlines the service levels and commitments that the Provider agrees to deliver.</p>
                    
                    <h4 class="text-primary mb-3">1. Definitions</h4>
                    <p class="mb-4">The following definitions apply to this SLA:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2"><strong>Uptime:</strong> The percentage of time services are available during a calendar month.</li>
                        <li class="mb-2"><strong>Downtime:</strong> The total minutes in a calendar month during which the Customer's services are unavailable, excluding Scheduled Maintenance.</li>
                        <li class="mb-2"><strong>Scheduled Maintenance:</strong> Planned maintenance activities that may affect service availability, which will be announced at least 48 hours in advance.</li>
                        <li class="mb-2"><strong>Emergency Maintenance:</strong> Unplanned maintenance required to address critical security or performance issues.</li>
                        <li class="mb-2"><strong>Service Credit:</strong> Credit issued to Customer's account as compensation for service level failures.</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">2. Service Availability</h4>
                    <p class="mb-4">The Provider aims to maintain service availability as follows:</p>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Service Type</th>
                                    <th>Monthly Uptime Commitment</th>
                                    <th>Maximum Consecutive Downtime</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>VPS Hosting</td>
                                    <td>99.9%</td>
                                    <td>45 minutes</td>
                                </tr>
                                <tr>
                                    <td>Web Hosting</td>
                                    <td>99.9%</td>
                                    <td>45 minutes</td>
                                </tr>
                                <tr>
                                    <td>Game Server Hosting</td>
                                    <td>99.9%</td>
                                    <td>45 minutes</td>
                                </tr>
                                <tr>
                                    <td>DNS Services</td>
                                    <td>99.99%</td>
                                    <td>20 minutes</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <p class="mb-4">Uptime is calculated using the following formula:</p>
                    <div class="bg-darker p-3 rounded mb-4">
                        <code>Uptime % = ((Total Minutes in Month - Downtime) / Total Minutes in Month) × 100</code>
                    </div>
                    
                    <h4 class="text-primary mb-3">3. Network Performance</h4>
                    <p class="mb-4">The Provider commits to maintaining high-quality network connectivity with the following standards:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Network availability of 99.9% measured monthly</li>
                        <li class="mb-2">Average network latency less than 30ms within Europe and less than 100ms globally</li>
                        <li class="mb-2">Packet loss rate less than 0.1% on average</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">4. DDoS Protection</h4>
                    <p class="mb-4">All services include DDoS protection with the following specifications:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Protection against layer 3 and layer 4 attacks up to 5 Tbps</li>
                        <li class="mb-2">Protection against common layer 7 attacks</li>
                        <li class="mb-2">Automated mitigation response time of less than 10 seconds</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">5. Support Response Times</h4>
                    <p class="mb-4">The Provider commits to the following technical support response times:</p>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Priority Level</th>
                                    <th>Description</th>
                                    <th>Initial Response Time</th>
                                    <th>Status Updates</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-danger">Critical</span></td>
                                    <td>Service is unavailable</td>
                                    <td>30 minutes</td>
                                    <td>Every 2 hours</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning">High</span></td>
                                    <td>Service is degraded or unstable</td>
                                    <td>2 hours</td>
                                    <td>Every 6 hours</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info">Medium</span></td>
                                    <td>Non-critical functionality affected</td>
                                    <td>6 hours</td>
                                    <td>Every 24 hours</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary">Low</span></td>
                                    <td>General questions or feature requests</td>
                                    <td>24 hours</td>
                                    <td>As needed</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <h4 class="text-primary mb-3">6. Maintenance Windows</h4>
                    <p class="mb-4">To ensure optimal performance and security, the Provider conducts regular maintenance as follows:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2"><strong>Scheduled Maintenance:</strong> Typically performed between 01:00-05:00 UTC on Wednesdays or Sundays, with at least 48 hours advance notice.</li>
                        <li class="mb-2"><strong>Emergency Maintenance:</strong> May be conducted at any time to address critical security vulnerabilities or resolve service-affecting issues. The Provider will make reasonable efforts to notify customers in advance when possible.</li>
                    </ul>
                    
                    <p class="mb-4">Downtime during scheduled maintenance windows does not count toward SLA calculations.</p>
                    
                    <h4 class="text-primary mb-3">7. Service Credits</h4>
                    <p class="mb-4">If the Provider fails to meet the service availability commitments, the Customer is eligible for Service Credits as follows:</p>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Monthly Uptime</th>
                                    <th>Service Credit (% of monthly fee)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>99.0% - 99.89%</td>
                                    <td>10%</td>
                                </tr>
                                <tr>
                                    <td>95.0% - 98.99%</td>
                                    <td>25%</td>
                                </tr>
                                <tr>
                                    <td>90.0% - 94.99%</td>
                                    <td>50%</td>
                                </tr>
                                <tr>
                                    <td>Below 90.0%</td>
                                    <td>100%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <h4 class="text-primary mb-3">8. Credit Request Process</h4>
                    <p class="mb-4">To receive Service Credits, the Customer must:</p>
                    
                    <ol class="mb-4">
                        <li class="mb-2">Submit a request within 7 days of the end of the month in which the downtime occurred</li>
                        <li class="mb-2">Include service ID, dates, times, and duration of the downtime</li>
                        <li class="mb-2">Send the request via the support ticket system or email to <a href="mailto:billing@wavehost.com" class="text-primary">billing@wavehost.com</a></li>
                    </ol>
                    
                    <p class="mb-4">The Provider will review all submissions and respond within 10 business days. Approved credits will be applied to the Customer's account and will be reflected on the next billing cycle.</p>
                    
                    <h4 class="text-primary mb-3">9. SLA Exclusions</h4>
                    <p class="mb-4">This SLA does not apply to performance issues caused by:</p>
                    
                    <ul class="mb-4">
                        <li class="mb-2">Factors outside the Provider's reasonable control (force majeure)</li>
                        <li class="mb-2">Customer's applications, equipment, or third-party software or services</li>
                        <li class="mb-2">Actions or inactions of the Customer or authorized users</li>
                        <li class="mb-2">Suspensions or terminations of services in accordance with the Terms of Service</li>
                        <li class="mb-2">Scheduled or emergency maintenance</li>
                        <li class="mb-2">DDoS attacks exceeding protection capacity</li>
                    </ul>
                    
                    <h4 class="text-primary mb-3">10. Changes to SLA</h4>
                    <p>The Provider reserves the right to modify this SLA at any time, with 30 days' notice to customers. The most current version will be posted on the Provider's website.</p>
                </div>
            </div>
            
            <p class="text-muted">Last updated: April, 2025</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>