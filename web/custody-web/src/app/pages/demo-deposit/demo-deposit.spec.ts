import { ComponentFixture, TestBed } from '@angular/core/testing';

import { DemoDeposit } from './demo-deposit';

describe('DemoDeposit', () => {
  let component: DemoDeposit;
  let fixture: ComponentFixture<DemoDeposit>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [DemoDeposit]
    })
    .compileComponents();

    fixture = TestBed.createComponent(DemoDeposit);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
