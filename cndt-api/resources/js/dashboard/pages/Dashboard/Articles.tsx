import { Head } from "@inertiajs/react";
import { Layout } from "@/components/Layout";
import { EntityPage } from "@/pages/EntityPage";
import { ENTITIES } from "@/lib/entities";

// Actualités — page Inertia /admin/articles
export default function Articles() {
  return (
    <Layout>
      <Head title="Actualités" />
      <EntityPage config={ENTITIES.articles} />
    </Layout>
  );
}
