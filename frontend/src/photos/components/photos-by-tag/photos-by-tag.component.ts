import {Component, OnInit, OnDestroy, ViewChild} from '@angular/core';
import {Router, ActivatedRoute} from '@angular/router';
import 'rxjs/add/operator/map';
import 'rxjs/add/operator/filter';
import {MetaTagsService} from '../../../core'
import {GalleryComponent} from '../../../lib';
import {
    AppService,
    TitleService,
    AuthProviderService,
    NavigatorServiceProvider,
    PagerServiceProvider,
    ProcessLockerServiceProvider,
    ScrollFreezerService,
} from '../../../shared';
import {PhotoDataProviderService} from '../../services';
import {PhotoToLinkedDataMapper, PhotoToGalleryImageMapper} from '../../mappers';
import {PhotosComponent as AbstractPhotosComponent} from '../abstract';

@Component({
    selector: 'photos-by-tag',
    templateUrl: 'photos-by-tag.component.html',
})
export class PhotosByTagComponent extends AbstractPhotosComponent implements OnInit, OnDestroy {
    @ViewChild('galleryComponent') galleryComponent: GalleryComponent;

    protected tagQueryParamSubsriber: any = null;

    constructor(public authProvider: AuthProviderService,
                protected photoDataProvider: PhotoDataProviderService,
                router: Router,
                route: ActivatedRoute,
                app: AppService,
                title: TitleService,
                metaTags: MetaTagsService,
                navigatorProvider: NavigatorServiceProvider,
                pagerProvider: PagerServiceProvider,
                processLockerProvider: ProcessLockerServiceProvider,
                scrollFreezer: ScrollFreezerService,
                galleryImageMapper: PhotoToGalleryImageMapper,
                linkedDataMapper: PhotoToLinkedDataMapper) {
        super(
            router,
            route,
            app,
            title,
            metaTags,
            navigatorProvider,
            pagerProvider,
            processLockerProvider,
            scrollFreezer,
            galleryImageMapper,
            linkedDataMapper
        );
        this.defaults['title'] = 'Search By Tag';
        this.defaults['tag'] = null;
    }

    reset(): void {
        super.reset();
        this.galleryComponent.reset();
    }

    ngOnInit(): void {
        super.ngOnInit();
        this.queryParams['tag'] = this.defaults['tag'];
    }

    ngOnDestroy(): void {
        super.ngOnDestroy();
        if (this.tagQueryParamSubsriber !== null) {
            this.tagQueryParamSubsriber.unsubscribe();
            this.tagQueryParamSubsriber = null;
        }
    }

    protected initParamsSubscribers() {
        super.initParamsSubscribers();
        this.tagQueryParamSubsriber = this.route.params
            .map((params) => params['tag'])
            .filter((tag) => typeof (tag) !== 'undefined')
            .map((tag) => String(tag))
            .filter((tag: string) => tag !== this.queryParams['tag'])
            .subscribe((tag: string) => this.onTagChange(tag));
    }

    initImages(fromPage: number, toPage: number, perPage: number, tag: string): void {
        if (fromPage <= toPage) {
            this.loadImages(fromPage, perPage, tag).then(() => this.initImages(++fromPage, toPage, perPage, tag));
        }
    }

    loadImages(page: number, perPage: number, tag: string) {
        if (tag) {
            return this.processLocker
                .lock(() => this.photoDataProvider.getByTag(page, perPage, tag))
                .then((response) => this.onLoadImagesSuccess(response));
        } else {
            return Promise.reject(new Error('Invalid value of a tag parameter.'));
        }
    }

    loadMoreImages(): Promise<any> {
        return this.loadImages(this.pager.getNextPage(), this.pager.getPerPage(), this.queryParams['tag']);
    }

    onTagChange(tag: string): void {
        this.reset();
        this.queryParams['tag'] = tag;
        this.defaults['title'] = `Tag #${tag}`;
        this.title.setPageNameSegment(this.defaults['title']);
        this.metaTags.setTitle(this.title.getPageNameSegment());
        this.initImages(this.defaults['page'], this.queryParams['page'], this.defaults['perPage'], tag);
    }
}
