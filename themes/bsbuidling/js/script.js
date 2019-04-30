var particle_canvas = document.getElementById("particle-canvas");
if(particle_canvas) {
	var options = {
		particleColor: '#ecf0f1',
		background: '#24292E',
		interactive: true,
		speed: 'fast',
		density: 'low'
	};
	var particleCanvas = new ParticleNetwork(particle_canvas, options);
}