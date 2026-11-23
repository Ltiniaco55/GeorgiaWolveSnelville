import {
    CanvasTexture,
    DirectionalLight,
    Group,
    Mesh,
    MeshBasicMaterial,
    NeutralToneMapping,
    PerspectiveCamera,
    PlaneGeometry,
    PMREMGenerator,
    SRGBColorSpace,
    Scene,
    WebGLRenderer,
} from 'three';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';
import { MeshoptDecoder } from 'three/examples/jsm/libs/meshopt_decoder.module.js';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';

/**
 * The spinning 3D jersey of the hero stage, in plain Three.js (no framework layer).
 * Port of docs/design/reference/CamisetaGiratoria-referencia.jsx (React Three Fiber),
 * keeping its behaviour:
 * - spins by itself, no pointer interaction (the canvas has pointer-events: none)
 * - fixed camera at chest height, [0, 0, 3.2], fov 26
 * - variable speed: slow front and back, fast across the sides
 *   (angle += speed * (0.3 + 2.4 * sin²(angle)) * dt, speed 0.55, dt capped at 0.05)
 * - soft float: y = sin(t * 1.1) * 0.02
 * - directional light [1, 1.5, 3] at 1.2, NeutralToneMapping, transparent background,
 *   pixel ratio capped at 2
 * - studio environment at 0.7 and a soft contact shadow below
 *
 * The environment is three's RoomEnvironment, generated locally: drei's "studio"
 * preset downloads an HDR from a third-party CDN. The contact shadow is a blurred
 * radial gradient on a plane (scale 1.6, opacity 0.45, at y -0.56), not drei's
 * depth-rendered ContactShadows, which would cost an extra render pass per frame.
 *
 * The model is compressed with EXT_meshopt_compression + KHR_mesh_quantization,
 * hence the MeshoptDecoder (bundled in JS, no extra files).
 */
const SPEED = 0.55;
const MAX_DELTA = 0.05;
const FLOAT_AMPLITUDE = 0.02;
const FLOAT_FREQUENCY = 1.1;

/**
 * Soft round shadow drawn on a canvas, used as the contact shadow texture.
 */
function createShadowTexture() {
    const size = 256;
    const canvas = document.createElement('canvas');
    canvas.width = size;
    canvas.height = size;

    const context = canvas.getContext('2d');
    const gradient = context.createRadialGradient(size / 2, size / 2, 0, size / 2, size / 2, size / 2);
    gradient.addColorStop(0, 'rgba(0, 0, 0, 1)');
    gradient.addColorStop(0.35, 'rgba(0, 0, 0, 0.55)');
    gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
    context.fillStyle = gradient;
    context.fillRect(0, 0, size, size);

    return new CanvasTexture(canvas);
}

/**
 * Builds the scene on `canvas` and loads the model.
 *
 * @param {HTMLCanvasElement} canvas
 * @param {{ modelUrl: string, onFirstFrame?: () => void }} options
 * @returns {Promise<{
 *   start: () => void,
 *   stop: () => void,
 *   renderStill: () => void,
 *   resize: (width: number, height: number) => void,
 *   dispose: () => void,
 * }>}
 */
export async function createJerseyScene(canvas, { modelUrl, onFirstFrame }) {
    const renderer = new WebGLRenderer({ canvas, antialias: true, alpha: true, failIfMajorPerformanceCaveat: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.setClearColor(0x000000, 0);
    renderer.toneMapping = NeutralToneMapping;
    renderer.outputColorSpace = SRGBColorSpace;

    const scene = new Scene();
    const camera = new PerspectiveCamera(26, 1, 0.1, 100);
    camera.position.set(0, 0, 3.2);

    const light = new DirectionalLight(0xffffff, 1.2);
    light.position.set(1, 1.5, 3);
    scene.add(light);

    const pmrem = new PMREMGenerator(renderer);
    const room = new RoomEnvironment();
    const environment = pmrem.fromScene(room, 0.04).texture;
    room.dispose();
    scene.environment = environment;
    scene.environmentIntensity = 0.7;

    const shadowTexture = createShadowTexture();
    const shadow = new Mesh(
        new PlaneGeometry(1.6, 1.6),
        new MeshBasicMaterial({ map: shadowTexture, transparent: true, opacity: 0.45, depthWrite: false }),
    );
    shadow.rotation.x = -Math.PI / 2;
    shadow.position.y = -0.56;
    scene.add(shadow);

    const jersey = new Group();
    scene.add(jersey);

    const loader = new GLTFLoader().setMeshoptDecoder(MeshoptDecoder);
    let gltf;

    try {
        gltf = await loader.loadAsync(modelUrl);
    } catch (error) {
        // Free what was already created before giving up (the stage keeps the poster).
        shadow.geometry.dispose();
        shadow.material.dispose();
        shadowTexture.dispose();
        environment.dispose();
        pmrem.dispose();
        renderer.dispose();
        renderer.forceContextLoss();

        throw error;
    }

    jersey.add(gltf.scene);

    let angle = 0;
    let elapsed = 0;
    let lastTime = null;
    let frame = null;
    let hasRenderedFirstFrame = false;
    let isDisposed = false;

    function render() {
        renderer.render(scene, camera);

        if (!hasRenderedFirstFrame) {
            hasRenderedFirstFrame = true;
            onFirstFrame?.();
        }
    }

    function tick(now) {
        const delta = lastTime === null ? 0 : Math.min((now - lastTime) / 1000, MAX_DELTA);
        lastTime = now;
        elapsed += delta;

        const sine = Math.sin(angle);
        angle = (angle + SPEED * (0.3 + 2.4 * sine * sine) * delta) % (Math.PI * 2);
        jersey.rotation.y = angle;
        jersey.position.y = Math.sin(elapsed * FLOAT_FREQUENCY) * FLOAT_AMPLITUDE;

        render();
        frame = requestAnimationFrame(tick);
    }

    return {
        start() {
            if (frame === null && !isDisposed) {
                lastTime = null;
                frame = requestAnimationFrame(tick);
            }
        },

        stop() {
            if (frame !== null) {
                cancelAnimationFrame(frame);
                frame = null;
            }
        },

        /** Front view at rest, without float (used for the poster). */
        renderStill() {
            jersey.rotation.y = 0;
            jersey.position.y = 0;
            render();
        },

        resize(width, height) {
            if (width === 0 || height === 0) {
                return;
            }

            renderer.setSize(width, height, false);
            camera.aspect = width / height;
            camera.updateProjectionMatrix();

            if (frame === null && hasRenderedFirstFrame) {
                render();
            }
        },

        dispose() {
            isDisposed = true;
            this.stop();

            scene.traverse((object) => {
                object.geometry?.dispose();

                const materials = Array.isArray(object.material) ? object.material : [object.material];
                materials.filter(Boolean).forEach((material) => {
                    Object.values(material)
                        .filter((value) => value?.isTexture)
                        .forEach((texture) => texture.dispose());
                    material.dispose();
                });
            });

            shadowTexture.dispose();
            environment.dispose();
            pmrem.dispose();
            renderer.dispose();
            renderer.forceContextLoss();
        },
    };
}
