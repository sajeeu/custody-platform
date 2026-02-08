import { ComponentFixture, TestBed } from '@angular/core/testing';

import { InstitutionalBars } from './institutional-bars';

describe('InstitutionalBars', () => {
  let component: InstitutionalBars;
  let fixture: ComponentFixture<InstitutionalBars>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [InstitutionalBars]
    })
    .compileComponents();

    fixture = TestBed.createComponent(InstitutionalBars);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
