<template>
  <div ref="panoramaContainer" class="panoramaContainer">
    <div class="custom-controls">
      <button @click="togglePlay">{{ isPlaying ? 'Pause' : 'Play' }}</button>
      <button @click="stopVideo">Stop</button>
      <div class="progress-bar" @click="seekTo">
        <div class="progress" :style="{ width: progress + '%' }"></div>
      </div>
    </div>
  </div>
</template>

<script>
import { Viewer, VideoPanorama } from 'panolens';

export default {
  name: 'Custom360VideoPlayer',
  data() {
    return {
      video: null,
      panorama: null,
      isPlaying: false,
      progress: 0,
    };
  },
  mounted() {
    this.initVideoPlayer();
  },
  methods: {
    initVideoPlayer() {
      // Create a viewer for the panorama
      const viewer = new Viewer({
        container: this.$refs.panoramaContainer,
      });

      // Create a VideoPanorama with your 360-degree video
      const panorama = new VideoPanorama("VID_20230501_181734_00_009.mp4", {
        autoplay: false, // Disable auto-play for custom control handling
      });

      // Add the VideoPanorama to the viewer
      viewer.add(panorama);

      // Get a reference to the video element
      this.video = panorama.getVideoElement();
      this.panorama = panorama
      this.video.addEventListener("loadedmetadata", (event) => {
        console.log(
          "The duration and dimensions of the media and tracks are now known.",
        );
      });

    },
    togglePlay() {
      if (this.isPlaying) {
        this.video.pause();
      } else {
        this.video.play();
      }
      this.isPlaying = !this.isPlaying;
    },
    stopVideo() {
      this.video.pause();
      this.video.currentTime = 0;
      this.isPlaying = false;
      this.progress = 0;
    },
    seekTo(event) {
      const video = this.panorama.getVideoElement();
      const boundingRect = event.currentTarget.getBoundingClientRect();
      const clickX = event.clientX - boundingRect.left;
      const fullWidth = boundingRect.width;
      const percent = (clickX / fullWidth) * 100;
      this.progress = percent;
      console.log('video.duration', video.duration);
      const time = parseInt((percent / 100) * video.duration);
      video.currentTime += 10;
      console.log('time', video.currentTime);
    },
  },
};
</script>

<style scoped>
.custom-controls {
  position: absolute;
  bottom: 20px;
  left: 0;
  right: 0;
  text-align: center;
}

.custom-controls button {
  margin: 5px;
  padding: 5px 10px;
  cursor: pointer;
}

.progress-bar {
  width: 100%;
  height: 5px;
  background: #ccc;
  position: relative;
}

.progress {
  height: 100%;
  background: #0f8;
}
</style>
