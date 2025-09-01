@extends('layouts.admin')

@section('title', 'Google Analytics Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Google Analytics Dashboard</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary" onclick="dashboard.loadData()">
                            <i class="fas fa-sync-alt"></i> Atualizar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Métricas -->
                    <div class="row mb-4">
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3 id="totalSessions">-</h3>
                                    <p>Total de Sessões</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3 id="totalUsers">-</h3>
                                    <p>Total de Usuários</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3 id="totalPageviews">-</h3>
                                    <p>Total de Pageviews</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-eye"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3 id="avgBounceRate">-</h3>
                                    <p>Taxa de Rejeição</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3 id="usersOnline">-</h3>
                                    <p>Usuários Online</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-circle text-success"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-6">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3 id="realTimeVisitors">-</h3>
                                    <p>Visitantes em Tempo Real</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Métricas do Google Ads -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h3 id="totalClicks">-</h3>
                                    <p>Total de Cliques</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-mouse-pointer"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h3 id="totalImpressions">-</h3>
                                    <p>Total de Impressões</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-dark">
                                <div class="inner">
                                    <h3 id="totalCost">-</h3>
                                    <p>Custo Total</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-light">
                                <div class="inner">
                                    <h3 id="totalConversions">-</h3>
                                    <p>Total de Conversões</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Sessões e Usuários por Dia</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="sessionsChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Fontes de Tráfego</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="trafficSourcesChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Performance do Google Ads</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="googleAdsChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Top Campanhas</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="topCampaignsChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Páginas mais visitadas -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Páginas Mais Visitadas</h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Página</th>
                                                    <th>Visualizações</th>
                                                    <th>Tempo na Página</th>
                                                    <th>Taxa de Rejeição</th>
                                                </tr>
                                            </thead>
                                            <tbody id="topPagesTable">
                                                <!-- Dados serão inseridos via JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
class AnalyticsDashboard {
    constructor() {
        this.charts = {};
        this.init();
    }

    init() {
        this.loadData();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Atualizar a cada 5 minutos
        setInterval(() => this.loadData(), 300000);
    }

    async loadData() {
        try {
            const startDate = new Date();
            startDate.setDate(startDate.getDate() - 30);
            const endDate = new Date();

            const [analyticsData, adsData, trafficData] = await Promise.all([
                this.fetchAnalyticsData(startDate, endDate),
                this.fetchGoogleAdsData(startDate, endDate),
                this.fetchTrafficSources(startDate, endDate)
            ]);

            if (analyticsData && adsData && trafficData) {
                this.updateMetrics(analyticsData, adsData);
                this.updateCharts(analyticsData, adsData, trafficData);
            }
        } catch (error) {
            console.error('Erro ao carregar dados:', error);
            this.showError('Erro ao carregar dados do dashboard');
        }
    }

    async fetchAnalyticsData(startDate, endDate) {
        const response = await fetch(`/dashboard/analytics/data?start_date=${startDate.toISOString().split('T')[0]}&end_date=${endDate.toISOString().split('T')[0]}`);
        const data = await response.json();
        return data.success ? data.data : null;
    }

    async fetchGoogleAdsData(startDate, endDate) {
        const response = await fetch(`/dashboard/analytics/ads-data?start_date=${startDate.toISOString().split('T')[0]}&end_date=${endDate.toISOString().split('T')[0]}`);
        const data = await response.json();
        return data.success ? data.data : null;
    }

    async fetchTrafficSources(startDate, endDate) {
        const response = await fetch(`/dashboard/analytics/traffic-sources?start_date=${startDate.toISOString().split('T')[0]}&end_date=${endDate.toISOString().split('T')[0]}`);
        const data = await response.json();
        return data.success ? data.data : null;
    }

    updateMetrics(analyticsData, adsData) {
        if (analyticsData?.summary) {
            document.getElementById('totalSessions').textContent = analyticsData.summary.total_sessions.toLocaleString();
            document.getElementById('totalUsers').textContent = analyticsData.summary.total_users.toLocaleString();
            document.getElementById('totalPageviews').textContent = analyticsData.summary.total_pageviews.toLocaleString();
            document.getElementById('avgBounceRate').textContent = analyticsData.summary.avg_bounce_rate + '%';
            document.getElementById('usersOnline').textContent = analyticsData.summary.users_online.toLocaleString();
            document.getElementById('realTimeVisitors').textContent = analyticsData.summary.real_time_visitors.toLocaleString();
        }

        if (adsData?.summary) {
            document.getElementById('totalClicks').textContent = adsData.summary.total_clicks.toLocaleString();
            document.getElementById('totalImpressions').textContent = adsData.summary.total_impressions.toLocaleString();
            document.getElementById('totalCost').textContent = 'R$ ' + adsData.summary.total_cost.toFixed(2);
            document.getElementById('totalConversions').textContent = adsData.summary.total_conversions.toLocaleString();
        }
    }

    updateCharts(analyticsData, adsData, trafficData) {
        this.createSessionsChart(analyticsData);
        this.createTrafficSourcesChart(trafficData);
        this.createGoogleAdsChart(adsData);
        this.createTopCampaignsChart(adsData);
        this.updateTopPagesTable(trafficData);
    }

    createSessionsChart(data) {
        if (this.charts.sessions) {
            this.charts.sessions.destroy();
        }

        const ctx = document.getElementById('sessionsChart').getContext('2d');
        this.charts.sessions = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data?.daily_data?.map(item => item.date) || [],
                datasets: [{
                    label: 'Sessões',
                    data: data?.daily_data?.map(item => item.sessions) || [],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Usuários',
                    data: data?.daily_data?.map(item => item.users) || [],
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    createTrafficSourcesChart(data) {
        if (this.charts.trafficSources) {
            this.charts.trafficSources.destroy();
        }

        const ctx = document.getElementById('trafficSourcesChart').getContext('2d');
        this.charts.trafficSources = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data?.sources?.map(item => item.name) || [],
                datasets: [{
                    data: data?.sources?.map(item => item.sessions) || [],
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#fd7e14'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    createGoogleAdsChart(data) {
        if (this.charts.googleAds) {
            this.charts.googleAds.destroy();
        }

        const ctx = document.getElementById('googleAdsChart').getContext('2d');
        this.charts.googleAds = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Cliques', 'Impressões', 'Conversões'],
                datasets: [{
                    label: 'Google Ads',
                    data: [
                        data?.summary?.total_clicks || 0,
                        data?.summary?.total_impressions || 0,
                        data?.summary?.total_conversions || 0
                    ],
                    backgroundColor: ['#007bff', '#28a745', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    createTopCampaignsChart(data) {
        if (this.charts.topCampaigns) {
            this.charts.topCampaigns.destroy();
        }

        const ctx = document.getElementById('topCampaignsChart').getContext('2d');
        this.charts.topCampaigns = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: data?.campaigns?.map(item => item.name) || [],
                datasets: [{
                    label: 'Cliques',
                    data: data?.campaigns?.map(item => item.clicks) || [],
                    backgroundColor: ['#007bff', '#28a745', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    updateTopPagesTable(trafficData) {
        const tableBody = document.getElementById('topPagesTable');
        if (!tableBody || !trafficData?.top_pages) return;

        tableBody.innerHTML = '';
        
        trafficData.top_pages.forEach(page => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <strong>${page.title}</strong><br>
                    <small class="text-muted">${page.url}</small>
                </td>
                <td>${page.views.toLocaleString()}</td>
                <td>${Math.floor(page.time_on_page / 60)}m ${page.time_on_page % 60}s</td>
                <td>
                    <span class="badge bg-${page.time_on_page > 120 ? 'success' : page.time_on_page > 60 ? 'warning' : 'danger'}">
                        ${Math.floor(page.time_on_page / 60)}m ${page.time_on_page % 60}s
                    </span>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    showError(message) {
        // Mostrar erro de forma simples
        console.error(message);
    }
}

// Inicializar quando a página carregar
document.addEventListener('DOMContentLoaded', () => {
    window.dashboard = new AnalyticsDashboard();
});
</script>
@endsection
