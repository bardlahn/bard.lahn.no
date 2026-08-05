    
    <div id="ball-overlay">
        <canvas id="paper-canvas" resize></canvas>
    </div>

    <script type="text/paperscript" canvas="paper-canvas">
        var circleCount = 15;
        var minRadius = 5;
        var maxRadius = 80;
        var minSpeed = 0.5;
        var maxSpeed = 2;
        var circles = [];

        function createCircles() {
            for (var i = 0; i < circleCount; i++) {
                var radius = minRadius + Math.random() * (maxRadius - minRadius);
                var x = radius + Math.random() * (view.size.width - radius * 2);
                var y = radius + Math.random() * (view.size.height - radius * 2);
                var angle = Math.random() * Math.PI * 2;
                var speed = minSpeed + Math.random() * (maxSpeed - minSpeed);

                var circle = new Path.Circle({
                    center: [x, y],
                    radius: radius,
                    fillColor: 'white'
                });

                circle.velocity = new Point(Math.cos(angle) * speed, Math.sin(angle) * speed);
                circle.radius = radius;

                circles.push(circle);
            }
        }

        function resolveWallCollisions(circle) {
            if (circle.position.x - circle.radius < 0) {
                circle.position.x = circle.radius;
                circle.velocity.x *= -1;
            } else if (circle.position.x + circle.radius > view.size.width) {
                circle.position.x = view.size.width - circle.radius;
                circle.velocity.x *= -1;
            }

            if (circle.position.y - circle.radius < 0) {
                circle.position.y = circle.radius;
                circle.velocity.y *= -1;
            } else if (circle.position.y + circle.radius > view.size.height) {
                circle.position.y = view.size.height - circle.radius;
                circle.velocity.y *= -1;
            }
        }

        function resolveCircleCollisions() {
            for (var i = 0; i < circles.length; i++) {
                for (var j = i + 1; j < circles.length; j++) {
                    var a = circles[i];
                    var b = circles[j];
                    var dx = b.position.x - a.position.x;
                    var dy = b.position.y - a.position.y;
                    var distance = Math.sqrt(dx * dx + dy * dy);
                    var minDistance = a.radius + b.radius;

                    if (distance < minDistance && distance > 0) {
                        var nx = dx / distance;
                        var ny = dy / distance;

                        var overlap = minDistance - distance;
                        a.position.x -= nx * overlap * 0.5;
                        a.position.y -= ny * overlap * 0.5;
                        b.position.x += nx * overlap * 0.5;
                        b.position.y += ny * overlap * 0.5;

                        var dvx = b.velocity.x - a.velocity.x;
                        var dvy = b.velocity.y - a.velocity.y;
                        var velAlongNormal = dvx * nx + dvy * ny;

                        if (velAlongNormal < 0) {
                            var restitution = 1;
                            var impulse = -(1 + restitution) * velAlongNormal;
                            impulse /= (1 / a.radius + 1 / b.radius);

                            var impulseX = impulse * nx;
                            var impulseY = impulse * ny;

                            a.velocity.x -= impulseX / a.radius;
                            a.velocity.y -= impulseY / a.radius;
                            b.velocity.x += impulseX / b.radius;
                            b.velocity.y += impulseY / b.radius;
                        }
                    }
                }
            }
        }

        function onFrame(event) {
            for (var i = 0; i < circles.length; i++) {
                var c = circles[i];
                c.position += c.velocity;
                resolveWallCollisions(c);
            }
            resolveCircleCollisions();
        }

        function onResize(event) {
            for (var i = 0; i < circles.length; i++) {
                var circle = circles[i];
                circle.position.x = Math.min(circle.position.x, view.size.width - circle.radius);
                circle.position.y = Math.min(circle.position.y, view.size.height - circle.radius);
                circle.position.x = Math.max(circle.position.x, circle.radius);
                circle.position.y = Math.max(circle.position.y, circle.radius);
            }
        }

        createCircles();
    </script>

    <script type="text/javascript">
        (function() {
            var overlay = document.getElementById('ball-overlay');
            var trigger = document.getElementById('bounce');

            trigger.addEventListener('click', function() {
                overlay.classList.add('active');
                closeBtn.classList.add('active');
                window.dispatchEvent(new Event('resize'));
            });

            // Close menu on escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && navMenu && navMenu.classList.contains('open')) {
                overlay.classList.remove('active');
                closeBtn.classList.remove('active');
                }
            });
        });
        ();
    </script>
