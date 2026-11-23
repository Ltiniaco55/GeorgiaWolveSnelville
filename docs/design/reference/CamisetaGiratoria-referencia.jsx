// Camiseta 3D de Georgia Wolves girando sola.
// - Sin interacción con el ratón ni el dedo.
// - Cámara fija a la altura del pecho: nunca se ve desde arriba ni desde abajo.
// - Va lenta de frente y de espalda y rápida al pasar por los lados.
//
// Dependencias: npm i three @react-three/fiber @react-three/drei
// Modelo: copia camiseta-georgia-wolves.glb en public/assets/3d/

import { Suspense, useRef } from 'react';
import { Canvas, useFrame } from '@react-three/fiber';
import { useGLTF, Environment, ContactShadows } from '@react-three/drei';
import * as THREE from 'three';

const URL_MODELO = '/assets/3d/camiseta-georgia-wolves.glb';

function Camiseta({ velocidad = 0.55 }) {
  const { scene } = useGLTF(URL_MODELO);
  const grupo = useRef();
  const angulo = useRef(0);

  useFrame((state, delta) => {
    const dt = Math.min(delta, 0.05);
    const s = Math.sin(angulo.current);
    // 0.3 = lento de frente/espalda, sube hasta ~2.7 al pasar por los lados
    angulo.current = (angulo.current + velocidad * (0.3 + 2.4 * s * s) * dt) % (Math.PI * 2);
    grupo.current.rotation.y = angulo.current;
    grupo.current.position.y = Math.sin(state.clock.elapsedTime * 1.1) * 0.02; // flotación suave
  });

  return (
    <group ref={grupo}>
      <primitive object={scene} />
    </group>
  );
}

export default function CamisetaGiratoria({ width = 500, height = 500, velocidad = 0.55 }) {
  return (
    <div style={{ width, height, pointerEvents: 'none' }}>
      <Canvas
        camera={{ position: [0, 0, 3.2], fov: 26 }}
        gl={{ antialias: true, alpha: true, toneMapping: THREE.NeutralToneMapping }}
        dpr={[1, 2]}
      >
        <directionalLight position={[1, 1.5, 3]} intensity={1.2} />
        <Suspense fallback={null}>
          <Environment preset="studio" environmentIntensity={0.7} />
          <Camiseta velocidad={velocidad} />
          <ContactShadows position={[0, -0.56, 0]} opacity={0.45} scale={1.6} blur={2.5} far={1} />
        </Suspense>
      </Canvas>
    </div>
  );
}

useGLTF.preload(URL_MODELO);

// Uso:
// import CamisetaGiratoria from './CamisetaGiratoria';
// <CamisetaGiratoria width={500} height={500} />
