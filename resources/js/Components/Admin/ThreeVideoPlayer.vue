<template>
  <div ref="vp_wrap" class="vp_wrap">
    <div class="vp_controls">
      <v-row justify="center">
        <v-col cols="auto" class="pa-2">
          <v-btn density="comfortable" icon="mdi-rewind" @click="onRewind"></v-btn>
        </v-col>
        <v-col cols="auto" class="pa-2">
          <v-btn v-if="isPlaying" density="comfortable" icon="mdi-stop" @click="onPause"></v-btn>
          <v-btn v-if="!isPlaying" density="comfortable" icon="mdi-play" @click="onPlay"></v-btn>
        </v-col>
        <v-col cols="auto" class="pa-2">
          <v-btn density="comfortable" icon="mdi-fast-forward" @click="onFast"></v-btn>
        </v-col>
        <v-col cols="auto" class="pa-2">
          <v-menu>
            <template #activator="{ props }">
              <v-btn v-bind="props" width="80" class="px-1 text-caption">
                {{ current_quality.text }}
              </v-btn>
            </template>
            <v-list>
              <v-list-item v-for="(item, index) in qualities" :key="index" :value="index">
                <v-list-item-title class="text-caption" @click="changeQuality(item.value)">{{
                  item.text
                }}</v-list-item-title>
              </v-list-item>
            </v-list>
          </v-menu>
        </v-col>
        <v-col v-if="capture" cols="auto" class="pa-2">
          <v-btn icon="mdi-record" @click="onCapture"></v-btn>
        </v-col>
      </v-row>
      <div class="vp_progress mt-2">
        <v-progress-linear v-model="progress" :height="10" @click="onSeekTo"></v-progress-linear>
      </div>
    </div>
  </div>
</template>

<script>
import { Viewer, VideoPanorama } from 'panolens'
import Hls from 'hls.js'

export default {
  props: ['url', 'capture'],
  data() {
    return {
      viewer: null,
      panorama: null,
      video: null,
      hls: null,
      isPlaying: false,
      progress: 0,
      disabled: false,
      step: 2,
      video_url: null,
      current_quality: { value: -1, text: '自動' },
      qualities: [],
    }
  },
  watch: {},
  async mounted() {
    console.log('xxx')
    try {
      // this.video_url = await this.get_video_url(this.url)
      this.video_url = 'https://3d-videos-new.s3.ap-northeast-1.amazonaws.com/tmp/test04/video.m3u8'
      this.initVideoPlayer()
    } catch (e) {
      console.log(e)
    }
  },
  methods: {
    async initVideoPlayer() {
      const self = this
      // Create a viewer for the panorama
      self.viewer = new Viewer({
        container: self.$refs.vp_wrap,
        controlBar: false,
      })

      // Create a VideoPanorama with your 360-degree video
      // self.panorama = new VideoPanorama(this.video_url, {
      // self.panorama = new VideoPanorama(this.video_url, {
      self.panorama = new VideoPanorama(this.video_url, {
        // autoplay: false, // Disable auto-play for custom control handling
      })

      // Add the VideoPanorama to the viewer
      self.viewer.add(self.panorama)

      // Get a reference to the video element
      const video = self.panorama.getVideoElement()
      if (Hls.isSupported()) {
        console.log('isSuppprted')
        const hls = new Hls()
        hls.loadSource(self.video_url)
        hls.attachMedia(video)
        console.log('hls.levels', hls.levels)
        console.log('this.hls.current_quality', hls.current_quality)
        hls.on(Hls.Events.MANIFEST_PARSED, function (event, data) {
          self.qualities = [{ value: -1, text: '自動' }]
          hls.levels.forEach(function (level, index) {
            self.qualities.push({ value: index, text: level.width + 'px' })
            console.log('self.qualities', self.qualities)
          })
        })
        hls.on(Hls.Events.LEVEL_SWITCHED, function (event, data) {
          console.log('LEVEL_SWITCHED')
          console.log('current-quality-', data.level)
        })
        self.hls = hls
      } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = self.video_url
      }

      video.addEventListener('timeupdate', function () {
        // ビデオの現在の再生時間と総時間を取得
        var currentTime = video.currentTime
        var duration = video.duration

        // プログレスバーの幅を更新
        self.progress = (currentTime / duration) * 100
      })
      self.video = video
    },
    onPause() {
      this.video.pause()
      this.isPlaying = false
    },
    async onPlay() {
      try {
        await this.video.play()
        this.isPlaying = true
      } catch (e) {
        console.log(e)
      }
    },
    onRewind() {
      this.video.currentTime = this.video.currentTime < this.step ? 0 : this.video.currentTime - this.step
    },
    onFast() {
      this.video.currentTime =
        this.video.duration - this.video.currentTime < this.step
          ? this.video.duration
          : this.video.currentTime + this.step
    },
    onSeekTo(event) {
      const boundingRect = event.currentTarget.getBoundingClientRect()
      const clickX = event.clientX - boundingRect.left
      const fullWidth = boundingRect.width
      const percent = (clickX / fullWidth) * 100
      this.progress = percent
      const time = parseInt((percent / 100) * this.video.duration)
      this.video.currentTime = time
    },
    changeQuality(value) {
      console.log('changeQuality')
      this.hls.nextLevel = value
      self.current_quality = self.qualities.find((x) => x.value == value)
      return false
    },
  },
}
</script>
