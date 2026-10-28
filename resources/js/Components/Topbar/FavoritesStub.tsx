import { MoreVertical } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';

export default function FavoritesStub() {
    const { t } = useTranslation();

    return (
        <Button
            type="button"
            variant="ghost"
            size="icon-sm"
            aria-label={t('favoritos')}
            onClick={() => undefined}
        >
            <MoreVertical aria-hidden />
        </Button>
    );
}
