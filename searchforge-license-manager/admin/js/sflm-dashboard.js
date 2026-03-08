/**
 * SearchForge License Manager – Dashboard Charts (Chart.js)
 */
(function ($) {
    'use strict';

    $(document).ready(function () {
        // Tier Distribution (Donut)
        var tierCtx = document.getElementById('sflm-chart-tiers');
        if (tierCtx && typeof Chart !== 'undefined') {
            new Chart(tierCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Free', 'Professional', 'Enterprise', 'Development'],
                    datasets: [{
                        data: window.sflmChartData?.tiers || [0, 0, 0, 0],
                        backgroundColor: ['#6c757d', '#007bff', '#28a745', '#6f42c1'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // Platform Distribution (Bar)
        var platformCtx = document.getElementById('sflm-chart-platforms');
        if (platformCtx && typeof Chart !== 'undefined') {
            new Chart(platformCtx, {
                type: 'bar',
                data: {
                    labels: ['Node.js', 'Docker', 'WordPress', 'Drupal'],
                    datasets: [{
                        label: 'Installationen',
                        data: window.sflmChartData?.platforms || [0, 0, 0, 0],
                        backgroundColor: ['#339933', '#2496ED', '#21759B', '#0678BE'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        // Activations Over Time (Line)
        var timeCtx = document.getElementById('sflm-chart-timeline');
        if (timeCtx && typeof Chart !== 'undefined') {
            new Chart(timeCtx, {
                type: 'line',
                data: {
                    labels: window.sflmChartData?.timeline?.labels || [],
                    datasets: [{
                        label: 'Neue Aktivierungen',
                        data: window.sflmChartData?.timeline?.data || [],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        fill: true,
                        tension: 0.3,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 } }
                    }
                }
            });
        }

        // World Map – SVG equirectangular projection with installation dots
        sflmInitWorldMap();
    });

    /**
     * Render SVG world map with installation markers.
     * Uses equirectangular projection (lat/lng → x/y).
     */
    function sflmInitWorldMap() {
        var container = document.getElementById('sflm-world-map');
        if (!container) return;

        var points = window.sflmChartData?.geoMap || [];
        if (!points.length) {
            container.innerHTML = '<p style="text-align:center;color:#646970;padding:40px 0;">Keine Geodaten vorhanden.</p>';
            return;
        }

        var W = 960, H = 480;

        // Build SVG
        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 ' + W + ' ' + H);
        svg.setAttribute('class', 'sflm-map-svg');

        // Background
        var bg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        bg.setAttribute('width', W);
        bg.setAttribute('height', H);
        bg.setAttribute('fill', '#f0f4f8');
        bg.setAttribute('rx', '4');
        svg.appendChild(bg);

        // Simplified world coastline paths (equirectangular, Natural Earth inspired)
        var worldGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        worldGroup.setAttribute('fill', '#d1d5db');
        worldGroup.setAttribute('stroke', '#b0b5bc');
        worldGroup.setAttribute('stroke-width', '0.5');

        // Simplified continent outlines (equirectangular projection)
        var continents = [
            // North America
            'M130,95 L145,85 L175,80 L210,75 L235,80 L250,95 L260,110 L270,120 L265,135 L255,150 L240,160 L225,165 L210,170 L195,175 L180,180 L170,185 L160,190 L145,195 L140,190 L130,185 L120,175 L115,165 L110,150 L115,135 L120,120 L125,105 Z',
            // Central America + Caribbean
            'M170,190 L175,195 L185,200 L195,205 L200,210 L205,215 L210,220 L215,225 L210,230 L200,228 L190,225 L180,220 L170,215 L165,210 L160,200 L165,195 Z',
            // South America
            'M220,230 L235,225 L250,230 L265,240 L275,255 L280,270 L285,290 L288,310 L285,330 L280,345 L270,360 L260,370 L250,375 L240,370 L235,360 L230,345 L225,330 L222,310 L220,290 L218,270 L215,255 L218,240 Z',
            // Europe
            'M430,75 L445,70 L460,72 L475,70 L490,75 L500,80 L510,85 L515,95 L510,105 L505,115 L500,120 L490,125 L480,128 L470,130 L460,128 L450,125 L440,120 L435,115 L430,105 L428,95 L430,85 Z',
            // Africa
            'M440,170 L455,165 L470,168 L485,170 L500,175 L510,185 L515,200 L518,220 L520,240 L518,260 L515,280 L510,300 L505,310 L498,318 L490,320 L480,318 L470,315 L460,310 L452,300 L448,285 L445,270 L442,250 L440,230 L438,210 L438,190 L440,180 Z',
            // Asia (simplified)
            'M520,60 L540,55 L560,50 L580,48 L600,45 L630,42 L660,45 L690,50 L720,55 L740,60 L760,70 L770,80 L775,95 L770,110 L760,120 L745,130 L730,140 L710,148 L690,152 L670,155 L650,158 L630,160 L610,158 L590,155 L570,150 L555,145 L540,138 L530,130 L525,120 L520,110 L518,95 L520,80 Z',
            // Middle East / India
            'M540,140 L560,150 L580,155 L600,160 L620,165 L635,175 L640,190 L635,205 L625,215 L610,218 L595,215 L580,210 L565,200 L555,190 L545,180 L540,170 L538,155 Z',
            // Southeast Asia / Indonesia
            'M680,185 L700,180 L720,178 L740,180 L755,185 L765,195 L770,205 L765,215 L755,222 L740,225 L720,228 L700,225 L685,220 L675,210 L672,200 L675,190 Z',
            // Australia
            'M720,295 L740,290 L760,288 L780,290 L800,295 L815,305 L822,320 L820,335 L815,348 L805,358 L790,362 L775,360 L760,355 L745,348 L735,338 L728,325 L725,310 Z',
            // Japan / Korea
            'M770,85 L778,80 L785,82 L790,88 L792,98 L790,108 L785,115 L778,118 L772,112 L768,102 L767,92 Z',
            // UK / Ireland
            'M420,80 L428,78 L432,82 L430,90 L425,95 L420,92 L418,86 Z',
            // Scandinavia
            'M470,45 L478,40 L485,42 L492,48 L495,58 L492,68 L488,72 L482,70 L476,65 L472,58 L470,50 Z',
            // Greenland
            'M270,30 L290,25 L310,28 L325,35 L330,48 L325,60 L315,68 L300,70 L285,65 L275,55 L270,42 Z',
            // New Zealand
            'M840,340 L845,335 L850,338 L852,348 L848,358 L842,362 L838,355 L837,345 Z',
        ];

        continents.forEach(function(d) {
            var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', d);
            worldGroup.appendChild(path);
        });

        svg.appendChild(worldGroup);

        // Grid lines
        var gridGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        gridGroup.setAttribute('stroke', '#e2e6ea');
        gridGroup.setAttribute('stroke-width', '0.3');
        gridGroup.setAttribute('fill', 'none');
        // Latitude lines
        [0, 30, 60, -30, -60].forEach(function(lat) {
            var y = latToY(lat, H);
            var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', 0);
            line.setAttribute('y1', y);
            line.setAttribute('x2', W);
            line.setAttribute('y2', y);
            gridGroup.appendChild(line);
        });
        // Longitude lines
        [-120, -60, 0, 60, 120].forEach(function(lng) {
            var x = lngToX(lng, W);
            var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', x);
            line.setAttribute('y1', 0);
            line.setAttribute('x2', x);
            line.setAttribute('y2', H);
            gridGroup.appendChild(line);
        });
        svg.appendChild(gridGroup);

        // Installation dots
        var maxCount = Math.max.apply(null, points.map(function(p) { return p.count; }));
        var dotsGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');

        points.forEach(function(p) {
            var x = lngToX(p.lng, W);
            var y = latToY(p.lat, H);
            var r = dotRadius(p.count, maxCount);

            // Glow effect
            var glow = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            glow.setAttribute('cx', x);
            glow.setAttribute('cy', y);
            glow.setAttribute('r', r + 3);
            glow.setAttribute('fill', 'rgba(102, 126, 234, 0.2)');
            dotsGroup.appendChild(glow);

            // Main dot
            var dot = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            dot.setAttribute('cx', x);
            dot.setAttribute('cy', y);
            dot.setAttribute('r', r);
            dot.setAttribute('fill', '#667eea');
            dot.setAttribute('stroke', '#fff');
            dot.setAttribute('stroke-width', '1.5');
            dot.setAttribute('class', 'sflm-map-dot-svg');
            dotsGroup.appendChild(dot);

            // Tooltip title
            var title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
            title.textContent = p.name + ': ' + p.count + ' Installation' + (p.count !== 1 ? 'en' : '');
            dot.appendChild(title);

            // Label for large dots
            if (p.count > 2 && r >= 6) {
                var text = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                text.setAttribute('x', x);
                text.setAttribute('y', y + 1);
                text.setAttribute('text-anchor', 'middle');
                text.setAttribute('dominant-baseline', 'central');
                text.setAttribute('fill', '#fff');
                text.setAttribute('font-size', Math.max(8, r - 1) + 'px');
                text.setAttribute('font-weight', '700');
                text.setAttribute('pointer-events', 'none');
                text.textContent = p.count;
                dotsGroup.appendChild(text);
            }
        });

        svg.appendChild(dotsGroup);
        container.appendChild(svg);

        // Tooltip on hover (enhanced)
        var tooltip = document.createElement('div');
        tooltip.className = 'sflm-map-tooltip';
        tooltip.style.display = 'none';
        container.appendChild(tooltip);

        container.addEventListener('mousemove', function(e) {
            var svgRect = svg.getBoundingClientRect();
            var mx = ((e.clientX - svgRect.left) / svgRect.width) * W;
            var my = ((e.clientY - svgRect.top) / svgRect.height) * H;

            var hit = null;
            var minDist = Infinity;
            points.forEach(function(p) {
                var px = lngToX(p.lng, W);
                var py = latToY(p.lat, H);
                var dist = Math.sqrt((mx - px) * (mx - px) + (my - py) * (my - py));
                var hitR = dotRadius(p.count, maxCount) + 5;
                if (dist < hitR && dist < minDist) {
                    minDist = dist;
                    hit = p;
                }
            });

            if (hit) {
                tooltip.innerHTML = '<strong>' + sflmEscHtml(hit.name) + '</strong><br>' +
                    hit.count + ' Installation' + (hit.count !== 1 ? 'en' : '');
                tooltip.style.display = 'block';
                tooltip.style.left = (e.clientX - container.getBoundingClientRect().left + 12) + 'px';
                tooltip.style.top = (e.clientY - container.getBoundingClientRect().top - 10) + 'px';
            } else {
                tooltip.style.display = 'none';
            }
        });

        container.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
        });
    }

    /**
     * Convert longitude to SVG X coordinate (equirectangular).
     */
    function lngToX(lng, width) {
        return ((lng + 180) / 360) * width;
    }

    /**
     * Convert latitude to SVG Y coordinate (equirectangular).
     */
    function latToY(lat, height) {
        return ((90 - lat) / 180) * height;
    }

    /**
     * Calculate dot radius based on count.
     */
    function dotRadius(count, maxCount) {
        if (maxCount <= 0) return 4;
        var min = 4, max = 18;
        var ratio = Math.sqrt(count / maxCount);
        return min + ratio * (max - min);
    }

    /**
     * Simple HTML escape.
     */
    function sflmEscHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
})(jQuery);
