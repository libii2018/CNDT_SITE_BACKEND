import { Head } from "@inertiajs/react";
import { Layout } from "@/components/Layout";
import { EntityPage } from "@/pages/EntityPage";
import { ENTITIES } from "@/lib/entities";

// Galerie (albums & photos) — page Inertia /admin/albums
export default function Albums() {
  return (
    <Layout>
      <Head title="Galerie" />
      <EntityPage config={ENTITIES.albums} />
    </Layout>
  );
}
