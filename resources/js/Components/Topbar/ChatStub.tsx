import { MessageCircle } from 'lucide-react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/Components/ui/button';

export default function ChatStub() {
    const { t } = useTranslation();

    return (
        <Button
            type="button"
            variant="ghost"
            size="icon-sm"
            aria-label={t('chat')}
            onClick={() => undefined}
        >
            <MessageCircle aria-hidden />
        </Button>
    );
}
