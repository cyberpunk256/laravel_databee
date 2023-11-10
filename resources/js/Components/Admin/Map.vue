<template>
    <div>
        <div id="map" class="m_map"></div>
        <v-dialog
            v-model="modal"
            width="auto"
        >
            <v-card class="video_card">
                <v-btn icon="mdi-close" @click="modal = false" class="video_close"></v-btn>
                <three-video-player v-if="modal_video_url" :url="modal_video_url"/>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import L from 'leaflet';
import 'leaflet-gpx';
import ThreeVideoPlayer from '@/Components/Admin/ThreeVideoPlayer.vue';

export default {
  created () {
  },
  components: { ThreeVideoPlayer },
  // props: ['items'],
  data() {
    return {
      map: null,
      zoom: 2,
      center: [47.41322, -1.219482],
      modal: false,
      modal_video_url: null,
      items: [
        {
            type: 'video',
            gpx_path: "tmp/VID_20230501/VID_20230501_175744_00_001.gpx",
            video_path: "tmp/test_minvideo_2mbyte.mp4"
        },
      ],
      view: [36.2048, 138.2529],
      gpxOptions: {
        async: true,
        marker_options: {
          startIconUrl: '/pin-icon-start.png',
          endIconUrl:   '/pin-icon-end.png',
          shadowUrl:    '/pin-shadow.png',
        },
        gpx_options: {
            joinTrackSegments: false
        }, 
      }
    };
  },
  mounted() {
    const self = this
    self.map = L.map('map').setView(this.view,6);
    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: 'Map data &copy; <a href="http://www.osm.org">OpenStreetMap</a>'
    }).addTo(self.map)
    for (let i = 0; i < self.items.length; i++) {
      const item = self.items[i];
      console.log('self.get_file(item.gpx_path)', self.get_file(item.gpx_path))
      if(item.type == 'video') {
        const init_gpx = () => {
          try {
            // const { data } = axios.get(self.get_file(item.gpx_path))
            new L.GPX(self.get_file(item.gpx_path), this.gpxOptions)
              .on('loaded', self.onLoaded)
              .on('click', function() {
                  self.onShowModal(item)
              });
          } catch(e) {
            console.log(e)
          }
        }
        init_gpx();
      }
    }
  },
  methods: {
    onLoaded(e) {
      this.map.fitBounds(e.target.getBounds());
      e.target.addTo(this.map);
    },
    onShowModal(item) {
      const self = this
      axios.post('/api/get_presigned_url', {
        path: item.video_path
      }).then((res) => {
        const { data } = res
        if(data.success) {
          // self.modal_video_url = data.presigned_url
          self.modal_video_url = '/demo.mp4'
          this.modal = true
        } else {
          self.show_toast();
        }
      })
    }
  },
};
</script>