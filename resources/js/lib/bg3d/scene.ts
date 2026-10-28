/**
 * The storefront's 3D background: one WebGL canvas, one slowly turning model.
 * Loaded lazily (dynamic import) so visitors who switch it off — or have no
 * WebGL — never download Three.js.
 */
import * as THREE from 'three';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';
import { buildMixed, buildModel, disposeObject, MODEL_KEYS, type BuiltModel, type ModelKey, type Palette } from './models';

export interface BackgroundOptions {
    /** a ModelKey, 'mixed' or 'custom' */
    model: string;
    customUrl?: string | null;
    position: 'left' | 'center' | 'right';
    /** 0 still · 1 slow · 2 normal · 3 fast */
    speed: number;
    /** follow the pointer and the scroll position */
    interactive: boolean;
}

export interface BackgroundHandle {
    update(options: Partial<BackgroundOptions>): void;
    dispose(): void;
}

const SPIN = [0, 0.14, 0.3, 0.55]; // radians per second

export function readPalette(): Palette {
    const css = getComputedStyle(document.documentElement);
    const color = (name: string, fallback: string) => new THREE.Color(css.getPropertyValue(name).trim() || fallback);

    return {
        primary: color('--store-primary', '#1d4ed8'),
        secondary: color('--store-secondary', '#d99a1f'),
        dark: color('--store-header', '#0b1f3a'),
    };
}

export function webglAvailable(): boolean {
    try {
        const canvas = document.createElement('canvas');

        return !!(window.WebGLRenderingContext && (canvas.getContext('webgl2') || canvas.getContext('webgl')));
    } catch {
        return false;
    }
}

export async function loadCustom(url: string): Promise<BuiltModel | null> {
    try {
        const { GLTFLoader } = await import('three/examples/jsm/loaders/GLTFLoader.js');
        const gltf = await new GLTFLoader().loadAsync(url);
        const root = gltf.scene;

        // normalise: ~3.2 units tall, centred on the origin
        const box = new THREE.Box3().setFromObject(root);
        const size = box.getSize(new THREE.Vector3());
        const centre = box.getCenter(new THREE.Vector3());
        const scale = 3.2 / Math.max(size.x, size.y, size.z || 1);

        root.position.sub(centre);
        const group = new THREE.Group();
        group.add(root);
        group.scale.setScalar(scale);

        const mixer = gltf.animations.length ? new THREE.AnimationMixer(root) : null;
        mixer && gltf.animations.forEach((clip) => mixer.clipAction(clip).play());

        return { group, tick: (_t, dt) => mixer?.update(dt) };
    } catch {
        return null;
    }
}

export async function createBackground(canvas: HTMLCanvasElement, initial: BackgroundOptions): Promise<BackgroundHandle> {
    let options = { ...initial };

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const renderer = new THREE.WebGLRenderer({ canvas, alpha: true, antialias: true, powerPreference: 'low-power' });
    renderer.setClearColor(0x000000, 0);
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.05;

    const scene = new THREE.Scene();
    const pmrem = new THREE.PMREMGenerator(renderer);
    const environment = pmrem.fromScene(new RoomEnvironment(), 0.04);
    scene.environment = environment.texture;

    const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 100);
    camera.position.set(0, 0.5, 11.5);

    const palette = readPalette();
    scene.add(new THREE.HemisphereLight(0xffffff, 0x334155, 0.7));
    const key = new THREE.DirectionalLight(0xffffff, 2.4);
    key.position.set(4, 6, 7);
    scene.add(key);
    const rim = new THREE.DirectionalLight(palette.secondary, 2.2);
    rim.position.set(-6, 2, -4);
    scene.add(rim);
    const fill = new THREE.PointLight(palette.primary, 30, 30);
    fill.position.set(3, -2, 5);
    scene.add(fill);

    // stage → tilt (pointer/scroll) → spinner (own rotation) → model
    const stage = new THREE.Group();
    const tilt = new THREE.Group();
    const spinner = new THREE.Group();
    tilt.add(spinner);
    stage.add(tilt);
    scene.add(stage);

    let current: BuiltModel | null = null;
    let buildToken = 0;

    async function setModel(): Promise<void> {
        const token = ++buildToken;
        let built: BuiltModel | null = null;

        if (options.model === 'custom') {
            built = options.customUrl ? await loadCustom(options.customUrl) : null;
        } else if (options.model === 'mixed') {
            built = buildMixed(palette);
        } else if ((MODEL_KEYS as string[]).includes(options.model)) {
            built = buildModel(options.model as ModelKey, palette);
        }

        if (token !== buildToken) {
            built && disposeObject(built.group);

            return;
        }

        if (current) {
            spinner.remove(current.group);
            disposeObject(current.group);
        }
        current = built;
        built && spinner.add(built.group);
        renderOnce();
    }

    // -- layout ---------------------------------------------------------------------
    function layout(): void {
        const w = window.innerWidth;
        const h = window.innerHeight;
        const pixelRatio = Math.min(window.devicePixelRatio || 1, w < 768 ? 1.5 : 1.75);

        renderer.setPixelRatio(pixelRatio);
        renderer.setSize(w, h, false);
        camera.aspect = w / h;
        camera.updateProjectionMatrix();

        const small = w < 768;
        const visibleWidth = 2 * Math.tan(THREE.MathUtils.degToRad(camera.fov / 2)) * camera.position.z * camera.aspect;
        // the mixed scene is a wide cluster: smaller, and pushed further to its side
        const wide = options.model === 'mixed';
        const offset = (options.position === 'left' ? -0.22 : options.position === 'right' ? 0.22 : 0) * (wide ? 1.3 : 1);

        stage.position.x = small ? 0 : visibleWidth * offset;
        stage.position.y = small ? 1.4 : 0;
        stage.scale.setScalar((small ? 0.62 : Math.min(1, 0.6 + w / 3600)) * (wide ? 0.78 : 1));
    }

    // -- interaction ----------------------------------------------------------------
    const pointer = { x: 0, y: 0, tx: 0, ty: 0 };
    const onPointer = (e: PointerEvent) => {
        pointer.tx = (e.clientX / window.innerWidth) * 2 - 1;
        pointer.ty = (e.clientY / window.innerHeight) * 2 - 1;
    };
    let scrollProgress = 0;
    let scrollTarget = 0;
    const onScroll = () => {
        scrollTarget = Math.min(1, window.scrollY / Math.max(1, window.innerHeight * 3));
    };

    // -- loop -----------------------------------------------------------------------
    let last = performance.now();
    let elapsed = 0;
    let raf = 0;
    let running = false;

    function frame(): void {
        const now = performance.now();
        const dt = Math.min((now - last) / 1000, 0.05);
        last = now;
        elapsed += dt;
        const t = elapsed;

        spinner.rotation.y += SPIN[Math.max(0, Math.min(3, options.speed))] * dt;
        current?.tick?.(t, dt);

        if (options.interactive) {
            pointer.x += (pointer.tx - pointer.x) * Math.min(1, dt * 3);
            pointer.y += (pointer.ty - pointer.y) * Math.min(1, dt * 3);
            scrollProgress += (scrollTarget - scrollProgress) * Math.min(1, dt * 2.5);
        } else {
            pointer.x *= 0.9;
            pointer.y *= 0.9;
        }

        tilt.rotation.y = pointer.x * 0.35;
        tilt.rotation.x = pointer.y * 0.18 + scrollProgress * 0.45;
        tilt.position.y = Math.sin(t * 0.8) * 0.12 + scrollProgress * 1.1;

        renderer.render(scene, camera);

        if (running) raf = requestAnimationFrame(frame);
    }

    function renderOnce(): void {
        if (!running) frame();
    }

    function start(): void {
        if (running || reducedMotion) return;
        running = true;
        last = performance.now();
        raf = requestAnimationFrame(frame);
    }

    function stop(): void {
        running = false;
        cancelAnimationFrame(raf);
    }

    const onVisibility = () => (document.hidden ? stop() : start());
    const onResize = () => {
        layout();
        renderOnce();
    };

    window.addEventListener('resize', onResize, { passive: true });
    window.addEventListener('pointermove', onPointer, { passive: true });
    window.addEventListener('scroll', onScroll, { passive: true });
    document.addEventListener('visibilitychange', onVisibility);

    layout();
    await setModel();
    onScroll();
    start();

    return {
        update(next) {
            const modelChanged = (next.model !== undefined && next.model !== options.model) || (next.customUrl !== undefined && next.customUrl !== options.customUrl);
            options = { ...options, ...next };
            layout();
            modelChanged ? void setModel() : renderOnce();
        },
        dispose() {
            stop();
            buildToken++;
            window.removeEventListener('resize', onResize);
            window.removeEventListener('pointermove', onPointer);
            window.removeEventListener('scroll', onScroll);
            document.removeEventListener('visibilitychange', onVisibility);
            disposeObject(scene);
            environment.dispose();
            pmrem.dispose();
            renderer.dispose();
            renderer.forceContextLoss();
        },
    };
}
